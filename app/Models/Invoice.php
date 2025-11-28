<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'member',
        'transaction',
        'invoice',
    ];

    /**
     * Get the member that owns the invoice.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member');
    }

    /**
     * Get the transaction that owns the invoice.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction');
    }
}

