<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    public function index(Request $request)
    {
        // For now, get the first member as a demo user
        // In a real app, this would be: auth()->user()
        $member = Member::with(['balances'])->first();
        
        // Calculate total balance
        $totalBalance = $member ? $member->balances()
            ->where('action', 'complete')
            ->get()
            ->sum(function ($balance) {
                if ($balance->process->value === 'income') {
                    return $balance->amount;
                } else {
                    return -$balance->amount;
                }
            }) : 0;

        return view('pages.balance.index', [
            'member' => $member,
            'totalBalance' => number_format($totalBalance, 2)
        ]);
    }
}

