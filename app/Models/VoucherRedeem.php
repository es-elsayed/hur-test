<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherRedeem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'voucher_redeem';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'voucher',
        'member',
        'redeem',
        'projects',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'redeem' => 'boolean',
        'projects' => 'array',
    ];

    /**
     * Get the voucher that owns the voucher redeem.
     */
    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class, 'voucher');
    }

    /**
     * Get the member that owns the voucher redeem.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member');
    }
}

