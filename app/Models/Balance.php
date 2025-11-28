<?php

namespace App\Models;

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
}

