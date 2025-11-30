<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\Member|null $member */
        $member = auth()->user();
        
        // Calculate total balance
        $totalBalance = 0;
        $projects = collect();
        
        if ($member) {
            $totalBalance = $member->balances()
            ->where('action', 'complete')
            ->get()
            ->sum(function ($balance) {
                if ($balance->process->value === 'income') {
                    return $balance->amount;
                } else {
                    return -$balance->amount;
                }
                });
            
            // Load member's projects
            $projects = $member->projects()->select('id', 'title')->get();
        }

        return view('pages.balance.index', [
            'member' => $member,
            'totalBalance' => number_format($totalBalance, 2),
            'projects' => $projects
        ]);
    }
}

