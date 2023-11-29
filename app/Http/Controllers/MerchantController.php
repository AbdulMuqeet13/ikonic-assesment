<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Order;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MerchantController extends Controller
{
    public function __construct(
        MerchantService $merchantService
    ) {}

    /**
     * Useful order statistics for the merchant API.
     *
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
        public function orderStats(Request $request): JsonResponse
        {
            // TODO: Complete this method
            $from = $request->input('from');
            $to = $request->input('to');

            $orders = Order::getOrdersInDateRange($from, $to);

            $count = $orders->count();
            $revenue = $orders->sum('subtotal');
            $commissionsOwed = $orders->sum('commission_owed');

            $noAffiliate = $orders->where('affiliate_id', null);
            $commissionsOwed -= $noAffiliate->sum('commission_owed');

            return response()->json([
                'count' => $count,
                'revenue' => $revenue,
                'commissions_owed' => $commissionsOwed,
            ]);
        }
}
