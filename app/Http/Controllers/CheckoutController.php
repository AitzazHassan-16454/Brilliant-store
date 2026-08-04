<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Exceptions\OrderException;
use App\Mail\OrderPlaced;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Throwable;

class CheckoutController extends Controller
{
    /**
     * Place an order from the current user's cart.
     */
    public function checkout(Request $request, OrderService $orderService)
    {
        $validated = $request->validate([
            'shipping_name' => ['required', 'string', 'max:255'],
            'shipping_email' => ['required', 'email', 'max:255'],
            'shipping_phone' => ['required', 'string', 'max:20'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'shipping_city' => ['required', 'string', 'max:255'],
            'shipping_postal_code' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:500'],
            'coupon_code' => ['nullable', 'string'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
        ]);

        try {
            $order = $orderService->placeOrder($request->user(), $validated);
        } catch (OrderException $e) {
            return back()->with('error', $e->getMessage());
        }

        try {
            Mail::to($order->shipping_email)->send(new OrderPlaced($order));
        } catch (Throwable $e) {
            Log::warning('Failed to send order confirmation email for order '.$order->id, ['error' => $e->getMessage()]);
        }

        return redirect('/orders')
            ->with('success', 'Order placed successfully!')
            ->with('tracking_code', $order->tracking_code);
    }
}
