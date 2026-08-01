<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderTrackingController extends Controller
{
    /**
     * Public page where customers can track an order using its tracking code.
     */
    public function show(Request $request)
    {
        $code = strtoupper(trim((string) $request->query('code')));

        if ($code === '') {
            return Inertia::render('Orders/Track', [
                'order' => null,
                'error' => null,
                'code' => null,
            ]);
        }

        $order = Order::with('items.product', 'statuses')
            ->where('tracking_code', $code)
            ->first();

        if (! $order) {
            return Inertia::render('Orders/Track', [
                'order' => null,
                'error' => 'No order was found with tracking code "'.$code.'". Please double-check the code and try again.',
                'code' => $code,
            ]);
        }

        return Inertia::render('Orders/Track', [
            'order' => $order,
            'error' => null,
            'code' => $order->tracking_code,
        ]);
    }
}
