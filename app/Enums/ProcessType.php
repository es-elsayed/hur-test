<?php

namespace App\Enums;

enum ProcessType: string
{
    case INCOME = 'income';
    case OUTCOME = 'outcome';

    /**
     * Get the label for the process type
     */
    public function label(): string
    {
        return match($this) {
            self::INCOME => 'Deposit',
            self::OUTCOME => 'Withdrawal',
        };
    }

    /**
     * Check if the process is a deposit
     */
    public function isDeposit(): bool
    {
        return $this === self::INCOME;
    }

    /**
     * Check if the process is a withdrawal
     */
    public function isWithdrawal(): bool
    {
        return $this === self::OUTCOME;
    }
}
