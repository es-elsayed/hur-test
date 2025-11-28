<?php

namespace App\Models;

use App\Enums\ActionStatus;
use App\Enums\ProcessType;
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

        return Transaction::whereJsonContains('data->balance_id', $this->id)->first();
    }

    /**
     * Calculate commission amount based on member type
     */
    protected function commissionAmount(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->member) {
                    return 0;
                }

                $commissionRate = config('fees.commission.' . $this->member->type, 0);
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
                    // For deposits: add commission and VAT
                    $total = $this->amount + $this->commission_amount + $this->vat_amount;
                    
                    // Subtract discount if applicable
                    $transaction = $this->transaction();
                    if ($transaction && isset($transaction->data['discount_amount'])) {
                        $total -= $transaction->data['discount_amount'];
                    }
                    
                    return round($total, 2);
                } else {
                    // For withdrawals: subtract commission and VAT (net payout)
                    return round($this->amount - $this->commission_amount - $this->vat_amount, 2);
                }
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

                $transaction = $this->transaction();
                return $transaction && isset($transaction->data['discount_amount']) 
                    ? round($transaction->data['discount_amount'], 2) 
                    : 0;
            }
        );
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
}

