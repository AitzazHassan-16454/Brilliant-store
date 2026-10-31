<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class OrderTrackingController extends Controller
{
    /**
     * Public page where customers can track an order using its tracking code.
     */
    public function show(Request $request): Response
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

    /**
     * Upload a payment slip for the given order.
     */
    public function uploadSlip(Request $request, string $code): RedirectResponse
    {
        $order = Order::where('tracking_code', strtoupper(trim($code)))->firstOrFail();

        $request->validate([
            'payment_slip' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        if ($order->payment_slip) {
            Storage::disk('public')->delete($order->payment_slip);
        }

        $path = $request->file('payment_slip')->store('payment_slips', 'public');

        $order->update([
            'payment_slip' => $path,
            'payment_slip_uploaded_at' => now(),
        ]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'status' => $order->status,
            'note' => 'Payment slip uploaded by customer',
        ]);

        return back()->with('success', 'Payment slip uploaded successfully! Our team will verify it shortly.');
    }
}
