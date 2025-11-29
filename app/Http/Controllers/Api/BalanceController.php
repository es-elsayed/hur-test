<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CalculateFeesRequest;
use App\Http\Requests\StoreDepositRequest;
use App\Http\Requests\StoreWithdrawalRequest;
use App\Http\Resources\BalanceDetailResource;
use App\Http\Resources\BalanceResource;
use App\Models\Balance;
use App\Models\Member;
use App\Models\Project;
use App\Services\BalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BalanceController extends Controller
{
    public function __construct(
        protected BalanceService $balanceService
    ) {}

    /**
     * Get all balance operations with calculated details
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $balances = Balance::with(['member', 'project'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => [
                'data' => BalanceResource::collection($balances->items()),
                'current_page' => $balances->currentPage(),
                'last_page' => $balances->lastPage(),
                'per_page' => $balances->perPage(),
                'total' => $balances->total(),
            ],
        ]);
    }

    /**
     * Get detailed information about a specific balance operation
     *
     * @param Balance $balance
     * @return JsonResponse
     */
    public function show(Balance $balance): JsonResponse
    {
        $balance->load(['member', 'project']);
        $details = $this->balanceService->getBalanceDetails($balance);

        return response()->json([
            'success' => true,
            'data' => new BalanceDetailResource($details),
        ]);
    }

    /**
     * Create a new deposit (income) operation
     *
     * @param StoreDepositRequest $request
     * @return JsonResponse
     */
    public function storeDeposit(StoreDepositRequest $request): JsonResponse
    {
        try {
            $member = Member::findOrFail($request->member_id);
            $project = Project::findOrFail($request->project_id);

            // Authorize using policy
            $this->authorize('createDeposit', [Balance::class, $member]);

            $balance = $this->balanceService->createDeposit(
                $member,
                $project,
                $request->amount,
                $request->payment_data ?? [],
                $request->voucher_id
            );

            $details = $this->balanceService->getBalanceDetails($balance);

            return response()->json([
                'success' => true,
                'message' => 'Deposit created successfully',
                'data' => $details,
            ], 201);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create deposit',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new withdrawal (outcome) operation
     *
     * @param StoreWithdrawalRequest $request
     * @return JsonResponse
     */
    public function storeWithdrawal(StoreWithdrawalRequest $request): JsonResponse
    {
        try {
            $member = Member::findOrFail($request->member_id);
            $project = $request->project_id ? Project::findOrFail($request->project_id) : null;

            // Authorize using policy
            $this->authorize('createWithdrawal', [Balance::class, $member]);

            $balance = $this->balanceService->createWithdrawal(
                $member,
                $request->amount,
                $project,
                $request->payout_data ?? []
            );

            $details = $this->balanceService->getBalanceDetails($balance);

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal created successfully',
                'data' => $details,
            ], 201);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create withdrawal',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Complete a balance operation
     *
     * @param Balance $balance
     * @return JsonResponse
     */
    public function complete(Balance $balance): JsonResponse
    {
        try {
            // Note: In a real application, you would get the authenticated member
            // For now, we'll get the member from the balance
            $member = $balance->member;
            
            // Authorize using policy
            $this->authorize('complete', [$balance, $member]);

            $this->balanceService->completeBalance($balance);

            return response()->json([
                'success' => true,
                'message' => 'Balance operation completed successfully',
                'data' => [
                    'id' => $balance->id,
                    'status' => $balance->action->value,
                ],
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to complete this operation',
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete balance operation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate fees for a potential deposit
     *
     * @param CalculateFeesRequest $request
     * @return JsonResponse
     */
    public function calculateDepositFees(CalculateFeesRequest $request): JsonResponse
    {
        try {
            $member = Member::findOrFail($request->member_id);

            $calculations = $this->balanceService->calculateDepositFees(
                $member,
                $request->amount,
                $request->project_id,
                $request->voucher_id
            );

            return response()->json([
                'success' => true,
                'data' => $calculations,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate fees',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate fees for a potential withdrawal
     *
     * @param CalculateFeesRequest $request
     * @return JsonResponse
     */
    public function calculateWithdrawalFees(CalculateFeesRequest $request): JsonResponse
    {
        try {
            $member = Member::findOrFail($request->member_id);

            $calculations = $this->balanceService->calculateWithdrawalFees(
                $member,
                $request->amount
            );

            return response()->json([
                'success' => true,
                'data' => $calculations,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate fees',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

