<?php

namespace App\Services;

use App\Enums\ActionStatus;
use App\Enums\ProcessType;
use App\Models\Balance;
use App\Models\Member;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\VoucherRedeem;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;
use Exception;

class BalanceService
{
    /**
     * Create a deposit (income) operation
     * Only clients can make deposits
     *
     * @param Member $member
     * @param Project $project
     * @param float $amount Base amount before commission and VAT
     * @param array $paymentData Additional payment data
     * @param int|null $voucherId Optional voucher ID to apply discount
     * @return Balance
     * @throws Exception
     */
    public function createDeposit(Member $member, Project $project, float $amount, array $paymentData = [], ?int $voucherId = null): Balance
    {
        // Verify member is a client
        if ($member->type !== 'client') {
            throw new Exception('Only clients can make deposits');
        }

        return DB::transaction(function () use ($member, $project, $amount, $paymentData, $voucherId) {
            // Calculate fees
            $calculations = $this->calculateDepositFees($member, $amount, $project->id, $voucherId);

            // Create balance record
            $balance = Balance::create([
                'member' => $member->id,
                'process' => ProcessType::INCOME,
                'amount' => $amount,
                'project' => $project->id,
                'action' => ActionStatus::UN_COMPLETE,
            ]);

            // Create transaction record (only for deposits)
            Transaction::create([
                'transaction' => 'TXN-'.strtoupper(uniqid()).'-'.time(),
                'project' => $project->id,
                'client' => $member->id,
                'amount' => $calculations['total_amount'],
                // Only store payment-related metadata, not fee calculations
                'data' => $paymentData ?: null,
            ]);

            return $balance->load(['member', 'project']);
        });
    }

    /**
     * Create a withdrawal (outcome) operation
     * Both clients and freelancers can make withdrawals
     *
     * @param Member $member
     * @param float $amount Base amount before fees
     * @param Project|null $project Optional project reference
     * @param array $payoutData Additional payout data
     * @return Balance
     * @throws Exception
     */
    public function createWithdrawal(Member $member, float $amount, ?Project $project = null, array $payoutData = []): Balance
    {
        // Verify member type
        if (!in_array($member->type, ['client', 'freelancer'])) {
            throw new Exception('Invalid member type for withdrawal');
        }

        return DB::transaction(function () use ($member, $amount, $project) {
            // Calculate fees
            $calculations = $this->calculateWithdrawalFees($member, $amount);

            // Create balance record (no transaction for withdrawals)
            $balance = Balance::create([
                'member' => $member->id,
                'process' => ProcessType::OUTCOME,
                'amount' => $amount,
                'project' => $project ? $project->id : null,
                'action' => ActionStatus::UN_COMPLETE,
            ]);

            return $balance->load(['member', 'project']);
        });
    }

    /**
     * Calculate deposit fees including commission, VAT, and discount
     *
     * @param Member $member
     * @param float $amount
     * @param int $projectId
     * @param int|null $voucherId
     * @return array
     */
    public function calculateDepositFees(Member $member, float $amount, int $projectId, ?int $voucherId = null): array
    {
        $commissionRate = config('fees.commission.' . $member->type, 0);
        $vatRate = config('fees.vat', 15);

        // Calculate commission
        $commissionAmount = ($amount * $commissionRate) / 100;

        // Calculate VAT on commission only
        $vatAmount = ($commissionAmount * $vatRate) / 100;

        // Calculate discount if voucher is provided
        $discountAmount = 0;
        if ($voucherId) {
            $discountAmount = $this->calculateVoucherDiscount($voucherId, $member->id, $projectId, $amount);
        }

        // Total amount = base + commission + VAT - discount
        $totalAmount = $amount + $commissionAmount + $vatAmount - $discountAmount;

        return [
            'base_amount' => round($amount, 2),
            'commission_amount' => round($commissionAmount, 2),
            'vat_amount' => round($vatAmount, 2),
            'discount_amount' => round($discountAmount, 2),
            'total_amount' => round($totalAmount, 2),
        ];
    }

    /**
     * Calculate withdrawal fees including commission and VAT
     *
     * @param Member $member
     * @param float $amount
     * @return array
     */
    public function calculateWithdrawalFees(Member $member, float $amount): array
    {
        $commissionRate = config('fees.commission.' . $member->type, 0);
        $vatRate = config('fees.vat', 15);

        // Calculate commission
        $commissionAmount = ($amount * $commissionRate) / 100;

        // Calculate VAT on commission only
        $vatAmount = ($commissionAmount * $vatRate) / 100;

        // Net payout = base - commission - VAT
        $netPayout = $amount - $commissionAmount - $vatAmount;

        return [
            'base_amount' => round($amount, 2),
            'commission_amount' => round($commissionAmount, 2),
            'vat_amount' => round($vatAmount, 2),
            'net_payout' => round($netPayout, 2),
        ];
    }

    /**
     * Calculate voucher discount for a deposit
     * Vouchers only apply to deposits (income operations)
     *
     * @param int $voucherId
     * @param int $memberId
     * @param int $projectId
     * @param float $amount
     * @return float
     */
    public function calculateVoucherDiscount(int $voucherId, int $memberId, int $projectId, float $amount): float
    {
        // Check if voucher is redeemed by this member
        $voucherRedeem = VoucherRedeem::where('voucher', $voucherId)
            ->where('member', $memberId)
            ->where('redeem', true)
            ->first();

        if (!$voucherRedeem) {
            return 0;
        }

        // Check if voucher is used for this project
        $projectIds = array_map('trim', explode(',', $voucherRedeem->projects ?? ''));
        if (!in_array((string)$projectId, $projectIds, true)) {
            return 0;
        }

        // Get voucher details
        $voucher = Voucher::find($voucherId);
        if (!$voucher) {
            return 0;
        }

        // Check if voucher is expired
        if ($voucher->expires_at && $voucher->expires_at->isPast()) {
            return 0;
        }

        // Calculate discount based on type
        if ($voucher->discount_type === 'percent') {
            return ($amount * $voucher->discount_value) / 100;
        } elseif ($voucher->discount_type === 'fixed') {
            return min($voucher->discount_value, $amount); // Don't exceed amount
        }

        return 0;
    }

    /**
     * Get balance details with all calculations
     *
     * @param Balance $balance
     * @return array
     */
    public function getBalanceDetails(Balance $balance): array
    {
        // Load relationships if not already loaded
        $balance->loadMissing(['member', 'project']);

        $member = $balance->member()->first();
        $amount = $balance->amount;
        $project = $balance->project()->first();

        if ($balance->process === ProcessType::INCOME) {
            // For deposits, get related transaction and use Balance accessors for calculations
            $transaction = $balance->transaction();

            return [
                'id' => $balance->id,
                'transactionRef' => $transaction ? $transaction->transaction : null,
                'processType' => $balance->process->value,
                'processAmount' => $balance->amount,
                'commissionAmount' => $balance->commission_amount,
                'vatAmount' => $balance->vat_amount,
                'discountAmount' => $balance->discount_amount,
                'totalAmount' => $balance->total_amount,
                'processStatus' => $balance->action->value,
                'processCreated' => $balance->created_at->toDateTimeString(),
                'memberName' => $member ? $member->name : 'N/A',
                'memberType' => $member ? $member->type : 'N/A',
                'projectId' => $project ? $project->id : null,
                'projectTitle' => $project ? $project->title : null,
                'additionalData' => $transaction && is_array($transaction->data) ? $transaction->data : [],
            ];
        } else {
            // For withdrawals
            $calculations = $this->calculateWithdrawalFees($member, $amount);

            return [
                'id' => $balance->id,
                'transactionRef' => null,
                'processType' => $balance->process->value,
                'processAmount' => $balance->amount,
                'commissionAmount' => $calculations['commission_amount'],
                'vatAmount' => $calculations['vat_amount'],
                'discountAmount' => 0,
                'totalAmount' => $calculations['net_payout'],
                'processStatus' => $balance->action->value,
                'processCreated' => $balance->created_at->toDateTimeString(),
                'memberName' => $member ? $member->name : 'N/A',
                'memberType' => $member ? $member->type : 'N/A',
                'projectId' => $project ? $project->id : null,
                'projectTitle' => $project ? $project->title : null,
                'additionalData' => [],
            ];
        }
    }

    /**
     * Complete a balance operation
     *
     * @param Balance $balance
     * @return Balance
     */
    public function completeBalance(Balance $balance): Balance
    {
        $balance->update(['action' => ActionStatus::COMPLETE]);
        return $balance;
    }
}

