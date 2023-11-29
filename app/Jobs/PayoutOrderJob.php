<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\ApiService;
use RuntimeException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class PayoutOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public Order $order
    )
    {
    }

    /**
     * Use the API service to send a payout of the correct amount.
     * Note: The order status must be paid if the payout is successful, or remain unpaid in the event of an exception.
     *
     * @return void
     */
    public function handle(ApiService $apiService)
    {
        try {
            // Use the API service to send a payout
            $apiService->sendPayout($this->order->affiliate->user->email, $this->order->commission_owed);

            // If the payout is successful, update the order status to paid
            $this->order->updateOrderPayoutStatus(Order::STATUS_PAID);
        } catch (RuntimeException $exception) {
            // If an exception occurs during payout, catch it and update the order status to unpaid
            $this->order->updateOrderPayoutStatus(Order::STATUS_UNPAID);

            // Re-throw the exception to indicate the failure
            throw $exception;
        }
    }
}
