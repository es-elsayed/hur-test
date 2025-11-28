<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'member',
        'title',
        'description',
        'budget',
        'status',
    ];

    /**
     * Get the member that owns the project.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member');
    }

    /**
     * Get the balances for the project.
     */
    public function balances(): HasMany
    {
        return $this->hasMany(Balance::class, 'project');
    }

    /**
     * Get the transactions for the project.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'project');
    }
}
