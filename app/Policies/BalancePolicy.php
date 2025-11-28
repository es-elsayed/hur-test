<?php

namespace App\Policies;

use App\Models\Balance;
use App\Models\Member;
use Illuminate\Auth\Access\Response;

class BalancePolicy
{
    /**
     * Determine whether the member can view any models.
     */
    public function viewAny(Member $member): bool
    {
        return true;
    }

    /**
     * Determine whether the member can view the model.
     */
    public function view(Member $member, Balance $balance): bool
    {
        // Members can view their own balance operations
        return $balance->member === $member->id;
    }

    /**
     * Determine whether the member can create a deposit.
     */
    public function createDeposit(Member $member): Response
    {
        // Only clients can make deposits
        return $member->type === 'client'
            ? Response::allow()
            : Response::deny('Only clients can make deposits.');
    }

    /**
     * Determine whether the member can create a withdrawal.
     */
    public function createWithdrawal(Member $member): Response
    {
        // Both clients and freelancers can make withdrawals
        return in_array($member->type, ['client', 'freelancer'])
            ? Response::allow()
            : Response::deny('Invalid member type for withdrawal.');
    }

    /**
     * Determine whether the member can complete the balance operation.
     */
    public function complete(Member $member, Balance $balance): bool
    {
        // Members can only complete their own balance operations
        return $balance->member === $member->id;
    }

    /**
     * Determine whether the member can update the model.
     */
    public function update(Member $member, Balance $balance): bool
    {
        // Members can only update their own balance operations
        return $balance->member === $member->id;
    }

    /**
     * Determine whether the member can delete the model.
     */
    public function delete(Member $member, Balance $balance): bool
    {
        // Members can only delete their own balance operations
        return $balance->member === $member->id;
    }
}
