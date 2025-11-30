<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Authenticatable
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the projects for the member.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'member');
    }

    /**
     * Get the balances for the member.
     */
    public function balances(): HasMany
    {
        return $this->hasMany(Balance::class, 'member');
    }

    /**
     * Get the transactions where this member is the client.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'client');
    }

    /**
     * Get the invoices for the member.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'member');
    }

    /**
     * Get the voucher redeems for the member.
     */
    public function voucherRedeems(): HasMany
    {
        return $this->hasMany(VoucherRedeem::class, 'member');
    }
}

