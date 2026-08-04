<?php

namespace App\Http\Controllers;

use App\Mail\OrderStatusUpdated;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Throwable;

class OrderController extends Controller
{
    /**
     * Show the current user's orders.
     */
    public function index()
    {
        return Inertia::render('Orders/Index', [
            'orders' => Order::with('items.product', 'statuses.user')
                ->where('user_id', auth()->id())
                ->latest()
                ->get(),
        ]);
    }

    /**
     * Delete one of the current user's orders.
     */
    public function destroy($id)
    {
        Order::where('id', $id)
            ->where('user_id', auth()->id())
            ->delete();

        return back()->with('success', 'Order deleted successfully!');
    }

    /**
     * Add the items from a previous order back to the current user's cart.
     */
    public function reorder($id)
    {
        $order = Order::where('id', $id)
            ->where('user_id', auth()->id())
            ->with('items.product')
            ->firstOrFail();

        foreach ($order->items as $item) {
            if (! $item->product || ! $item->product->is_active) {
                continue;
            }

            $cart = Cart::where('user_id', auth()->id())
                ->where('product_id', $item->product_id)
                ->first();

            if ($cart) {
                $newQty = $cart->qty + $item->quantity;
                $cart->update(['qty' => $newQty <= $item->product->stock ? $newQty : $item->product->stock]);
            } else {
                Cart::create([
                    'user_id' => auth()->id(),
                    'product_id' => $item->product_id,
                    'qty' => min($item->quantity, $item->product->stock),
                ]);
            }
        }

        return back()->with('success', 'Items added to your cart!');
    }

    /**
     * Show all orders to an admin.
     */
    public function adminIndex()
    {
        abort_if(! auth()->user()->can('orders.view'), 403);

        return Inertia::render('Products/Orders', [
            'orders' => Order::with('user', 'items.product', 'statuses.user')->latest()->get(),
        ]);
    }

    /**
     * Update an order's status and record it in the history.
     */
    public function updateStatus($id, Request $request)
    {
        abort_if(! auth()->user()->can('orders.update'), 403);

        $order = Order::findOrFail($id);
        $previousStatus = $order->status;
        $newStatus = $request->status;

        $order->update(['status' => $newStatus]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'status' => $newStatus,
            'note' => $previousStatus !== $newStatus
                ? "Status changed from \"{$previousStatus}\" to \"{$newStatus}\""
                : "Status re-set to \"{$newStatus}\"",
        ]);

        if ($order->shipping_email) {
            try {
                Mail::to($order->shipping_email)->send(new OrderStatusUpdated($order->fresh()));
            } catch (Throwable $e) {
                Log::warning('Failed to send status update email for order '.$order->id, ['error' => $e->getMessage()]);
            }
        }

        return back();
    }
}
