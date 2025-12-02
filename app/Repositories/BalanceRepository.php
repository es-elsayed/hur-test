<?php

namespace App\Repositories;

use App\Models\VoucherRedeem;
use App\Models\Voucher;
use App\Models\Transaction;
use App\Enums\ProcessType;
use Illuminate\Support\Collection;

class BalanceRepository
{
    /**
     * Static caches to avoid N+1 queries within the same request.
     */
    protected static array $voucherRedeemCache = [];
    protected static array $voucherCache = [];
    protected static array $transactionCache = [];

    /**
     * Get the voucher redeem cache.
     */
    public function getVoucherRedeemCache(): array
    {
        return static::$voucherRedeemCache;
    }

    /**
     * Get the voucher cache.
     */
    public function getVoucherCache(): array
    {
        return static::$voucherCache;
    }

    /**
     * Get the transaction cache.
     */
    public function getTransactionCache(): array
    {
        return static::$transactionCache;
    }

    /**
     * Clear all caches (useful for testing).
     */
    public function clearCaches(): void
    {
        static::$voucherRedeemCache = [];
        static::$voucherCache = [];
        static::$transactionCache = [];
    }

    /**
     * Batch load all related data for a collection of Balance models.
     * This prevents N+1 queries when serializing multiple balances.
     * 
     * @param Collection|array $balances
     * @return void
     */
    public function loadVoucherRedeems($balances): void
    {
        $balances = collect($balances);
        
        // Get income balances only (only they need vouchers/transactions)
        $incomeBalances = $balances->filter(function ($balance) {
            return $balance->process === ProcessType::INCOME;
        });

        if ($incomeBalances->isEmpty()) {
            return;
        }

        // Batch load all related data
        $this->batchLoadVoucherRedeems($incomeBalances);
        $this->batchLoadVouchers();
        $this->batchLoadTransactions($incomeBalances);
    }

    /**
     * Batch load voucher redeems for income balances.
     */
    protected function batchLoadVoucherRedeems(Collection $incomeBalances): void
    {
        // Get unique member-project pairs
        $memberProjectPairs = $incomeBalances
            ->map(function ($balance) {
                return [
                    'member' => $balance->getAttribute('member'),
                    'project' => $balance->project,
                ];
            })
            ->filter(fn($pair) => $pair['member'] && $pair['project'])
            ->unique(fn($pair) => $pair['member'] . '-' . $pair['project']);

        if ($memberProjectPairs->isEmpty()) {
            return;
        }

        $memberIds = $memberProjectPairs->pluck('member')->unique();

        // Single query to get all voucher redeems
        $voucherRedeems = VoucherRedeem::whereIn('member', $memberIds)
            ->where('redeem', true)
            ->get();

        // Cache by member-project pair
        foreach ($memberProjectPairs as $pair) {
            $key = $pair['member'] . '-' . $pair['project'];
            
            $voucherRedeem = $voucherRedeems
                ->where('member', $pair['member'])
                ->first(function ($redeem) use ($pair) {
                    $projects = $redeem->projects;
                    
                    // Handle both array (JSON) and string (comma-separated) formats
                    if (is_string($projects)) {
                        $projects = array_map('trim', explode(',', $projects));
                    }
                    
                    if (!is_array($projects)) {
                        return false;
                    }
                    
                    return in_array($pair['project'], $projects);
                });
            
            static::$voucherRedeemCache[$key] = $voucherRedeem;
        }
    }

    /**
     * Batch load vouchers from cached voucher redeems.
     */
    protected function batchLoadVouchers(): void
    {
        $voucherIds = collect(static::$voucherRedeemCache)
            ->filter()
            ->pluck('voucher')
            ->unique()
            ->values();

        if ($voucherIds->isEmpty()) {
            return;
        }

        // Single query to get all vouchers
        $vouchers = Voucher::whereIn('id', $voucherIds)->get()->keyBy('id');

        foreach ($vouchers as $id => $voucher) {
            static::$voucherCache[$id] = $voucher;
        }
    }

    /**
     * Batch load transactions for income balances.
     */
    protected function batchLoadTransactions(Collection $incomeBalances): void
    {
        $projectIds = $incomeBalances->pluck('project')->unique()->filter();
        $memberIds = $incomeBalances
            ->map(fn($balance) => $balance->getAttribute('member'))
            ->unique()
            ->filter();

        if ($projectIds->isEmpty() || $memberIds->isEmpty()) {
            return;
        }

        // Single query to get all relevant transactions
        $transactions = Transaction::whereIn('project', $projectIds)
            ->whereIn('client', $memberIds)
            ->orderBy('created_at', 'desc')
            ->get();

        // Cache by project-client-amount key
        foreach ($transactions as $transaction) {
            $key = $transaction->project . '-' . $transaction->client . '-' . $transaction->amount;
            
            // Store latest transaction for each key (already ordered by created_at desc)
            if (!isset(static::$transactionCache[$key])) {
                static::$transactionCache[$key] = $transaction;
            }
        }
    }

    /**
     * Get voucher redeem from cache or database.
     * 
     * @param int $memberId
     * @param int $projectId
     * @return VoucherRedeem|null
     */
    public function getVoucherRedeem(int $memberId, int $projectId): ?VoucherRedeem
    {
        $cacheKey = $memberId . '-' . $projectId;
        
        // Check cache first
        if (array_key_exists($cacheKey, static::$voucherRedeemCache)) {
            return static::$voucherRedeemCache[$cacheKey];
        }

        // Fallback to query if not cached
        $voucherRedeem = VoucherRedeem::where('member', $memberId)
            ->where('redeem', true)
            ->whereJsonContains('projects', $projectId)
            ->first();

        static::$voucherRedeemCache[$cacheKey] = $voucherRedeem;

        return $voucherRedeem;
    }

    /**
     * Get voucher from cache or database.
     * 
     * @param int $voucherId
     * @return Voucher|null
     */
    public function getVoucher(int $voucherId): ?Voucher
    {
        // Check cache first
        if (array_key_exists($voucherId, static::$voucherCache)) {
            return static::$voucherCache[$voucherId];
        }

        // Fallback to query if not cached
        $voucher = Voucher::find($voucherId);

        static::$voucherCache[$voucherId] = $voucher;

        return $voucher;
    }

    /**
     * Get transaction from cache or database.
     * 
     * @param int $projectId
     * @param int $clientId
     * @param float $amount
     * @return Transaction|null
     */
    public function getTransaction(int $projectId, int $clientId, float $amount): ?Transaction
    {
        $cacheKey = $projectId . '-' . $clientId . '-' . $amount;
        
        // Check cache first
        if (array_key_exists($cacheKey, static::$transactionCache)) {
            return static::$transactionCache[$cacheKey];
        }

        // Fallback to query if not cached
        $transaction = Transaction::where('project', $projectId)
            ->where('client', $clientId)
            ->where('amount', $amount)
            ->latest()
            ->first();

        static::$transactionCache[$cacheKey] = $transaction;

        return $transaction;
    }
}

