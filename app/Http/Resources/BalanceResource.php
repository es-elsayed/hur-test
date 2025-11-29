<?php

namespace App\Http\Resources;

use App\Enums\ProcessType;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BalanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get transaction reference if it's a deposit
        $transactionRef = null;
        if ($this->process === ProcessType::INCOME) {
            $transaction = Transaction::whereJsonContains('data->balance_id', $this->id)->first();
            $transactionRef = $transaction?->transaction;
        }

        return [
            'id' => $this->id,
            'transactionRef' => $transactionRef,
            'processType' => $this->process->value,
            'processAmount' => $this->amount,
            'totalAmount' => $this->total_amount,
            'processStatus' => $this->action->value,
            'processCreated' => $this->created_at->toDateTimeString(),
        ];
    }
}
