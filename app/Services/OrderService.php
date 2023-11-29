<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {}

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        // TODO: Add any other order-related logic here

        // Check if there is an order with the provided order_id
        $existingOrder = Order::where('external_order_id', $data['order_id'])->first();

        // If an order with the same order_id already exists, ignore the duplicate
        if ($existingOrder) {
            return;
        }

        $merchant = Merchant::getMerchantByDomain($data['merchant_domain']);

        // Check if there is an affiliate with the provided discount code
        $affiliate = $this->affiliateService->register($merchant, $data['customer_email'], $data['customer_name'], 0.1);

        try {
            $commission = $data['subtotal_price'] * $affiliate->commission_rate;
        }
        catch (\Exception) {
            $affiliate = Affiliate::getAffiliateByDiscountCode($data['discount_code']);
            $commission = $data['subtotal_price'] * $affiliate->commission_rate;

        }
        // Logging the commission
        Log::info('Commission');
        Log::info($commission);

        // Create a new order
        $order = [
            'subtotal' => $data['subtotal_price'],
            'affiliate_id' => $affiliate->id,
            'merchant_id' => $merchant->id,
            'commission_owed' => $commission,
            'external_order_id' => $data['order_id'],
        ];
        return Order::createOrder($order);
    }
}
