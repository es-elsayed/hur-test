<?php

namespace App\Enums;

enum ActionStatus: string
{
    case COMPLETE = 'complete';
    case UN_COMPLETE = 'un-complete';

    /**
     * Get the label for the action status
     */
    public function label(): string
    {
        return match($this) {
            self::COMPLETE => 'Complete',
            self::UN_COMPLETE => 'Incomplete',
        };
    }

    /**
     * Check if the action is complete
     */
    public function isComplete(): bool
    {
        return $this === self::COMPLETE;
    }

    /**
     * Check if the action is incomplete
     */
    public function isIncomplete(): bool
    {
        return $this === self::UN_COMPLETE;
    }
}

