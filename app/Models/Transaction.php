<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'transaction',
        'project',
        'client',
        'amount',
        'data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Get the project that owns the transaction.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project');
    }

    /**
     * Get the client (member) that owns the transaction.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'client');
    }

    /**
     * Get the invoice associated with the transaction.
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'transaction');
    }
}

