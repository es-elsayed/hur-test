<?php

namespace App\Models;

use App\Enums\ActionStatus;
use App\Enums\ProcessType;
use App\Models\VoucherRedeem;
use App\Services\BalanceService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Balance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'member',
        'process',
        'amount',
        'project',
        'action',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'process' => ProcessType::class,
        'action' => ActionStatus::class,
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'commission_amount',
        'vat_amount',
        'total_amount',
    ];

    /**
     * Get the member that owns the balance.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member');
    }

    /**
     * Get the project that owns the balance.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project');
    }

    /**
     * Get the related transaction for this balance (deposits only)
     */
    public function transaction()
    {
        if ($this->process !== ProcessType::INCOME) {
            return null;
        }

        // Ensure we have the Member model instance (not just the foreign key int)
        $member = $this->getRelationValue('member') ?? $this->member()->first();
        $projectId = $this->project;

        if (! $member || ! $projectId) {
            return null;
        }

        $fees = $this->getDepositFees();
        if (! $fees) {
            return null;
        }

        $expectedAmount = $fees['total_amount'];

        return Transaction::where('project', $projectId)
            ->where('client', $member->id)
            ->where('amount', $expectedAmount)
            ->latest()
            ->first();
    }

    /**
     * Calculate commission amount based on member type
     */
    protected function commissionAmount(): Attribute
    {
        return Attribute::make(
            get: function () {
                $memberModel = $this->getRelationValue('member');
                if (!$memberModel) {
                    return 0;
                }

                $commissionRate = config('fees.commission.' . $memberModel->type, 0);
                return round(($this->amount * $commissionRate) / 100, 2);
            }
        );
    }

    /**
     * Calculate VAT amount (15% on commission only)
     */
    protected function vatAmount(): Attribute
    {
        return Attribute::make(
            get: function () {
                $vatRate = config('fees.vat', 15);
                return round(($this->commission_amount * $vatRate) / 100, 2);
            }
        );
    }

    /**
     * Calculate total amount based on process type
     * For income: base + commission + VAT - discount
     * For outcome: base - commission - VAT (net payout)
     */
    protected function totalAmount(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->process === ProcessType::INCOME) {
                    $fees = $this->getDepositFees();

                    if ($fees) {
                        return $fees['total_amount'];
                    }

                    // Fallback if fees could not be calculated
                    return round($this->amount + $this->commission_amount + $this->vat_amount, 2);
                }

                // For withdrawals: subtract commission and VAT (net payout)
                return round($this->amount - $this->commission_amount - $this->vat_amount, 2);
            }
        );
    }

    /**
     * Get discount amount from transaction data (deposits only)
     */
    protected function discountAmount(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->process !== ProcessType::INCOME) {
                    return 0;
                }

                $fees = $this->getDepositFees();

                return $fees ? $fees['discount_amount'] : 0;
            }
        );
    }

    /**
     * Resolve voucher ID for this balance (if any) based on member and project.
     */
    protected function resolveVoucherId(): ?int
    {
        if ($this->process !== ProcessType::INCOME) {
            return null;
        }

        // Use the raw foreign key value to avoid mixing relation/model vs int
        $memberId = $this->getAttribute('member');
        $projectId = $this->project;

        if (! $memberId || ! $projectId) {
            return null;
        }

        $voucherRedeem = VoucherRedeem::where('member', $memberId)
            ->where('redeem', true)
            ->get()
            ->first(function ($redeem) use ($projectId) {
                $projectIds = explode(',', $redeem->projects);
                return in_array($projectId, $projectIds);
            });

        return $voucherRedeem?->voucher;
    }

    /**
     * Get deposit fee calculations for this balance using BalanceService.
     */
    protected function getDepositFees(): ?array
    {
        if ($this->process !== ProcessType::INCOME) {
            return null;
        }

        // Ensure we have the Member model instance (not just the foreign key int)
        $member = $this->getRelationValue('member') ?? $this->member()->first();
        $projectId = $this->project;

        if (! $member || ! $projectId) {
            return null;
        }

        /** @var BalanceService $service */
        $service = app(BalanceService::class);
        $voucherId = $this->resolveVoucherId();

        return $service->calculateDepositFees($member, $this->amount, $projectId, $voucherId);
    }

    /**
     * Get net payout amount (for withdrawals)
     */
    protected function netPayout(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->process === ProcessType::OUTCOME) {
                    return $this->total_amount;
                }
                return 0;
            }
        );
    }

    /**
     * Get total paid amount (for deposits)
     */
    protected function totalPaid(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->process === ProcessType::INCOME) {
                    return $this->total_amount;
                }
                return 0;
            }
        );
    }

    /**
     * Transaction reference accessor (for deposits only).
     */
    protected function transactionRef(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->process !== ProcessType::INCOME) {
                    return null;
                }

                $transaction = $this->transaction();

                return $transaction?->transaction ?? null;
            }
        );
    }
}

