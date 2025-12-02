<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Repositories\BalanceRepository;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    public function __construct(
        protected BalanceRepository $balanceRepository
    ) {}

    public function index(Request $request)
    {
        /** @var \App\Models\Member|null $member */
        $member = auth()->user();
        
        // Calculate total balance
        $totalBalance = 0;
        $projects = collect();
        
        if ($member) {
            $balances = $member->balances()
                ->where('action', 'complete')
                ->get();
            
            // Batch load voucher redeems to prevent N+1 queries
            $this->balanceRepository->loadVoucherRedeems($balances);
            
            $totalBalance = $balances->sum(function ($balance) {
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

