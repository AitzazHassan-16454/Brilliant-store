<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Exceptions\OrderException;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Create an order from the user's cart.
     *
     * Validates stock server-side, then persists the order,
     * its items and status history inside a transaction.
     *
     * @param  array<string, mixed>  $validated
     *
     * @throws OrderException When the cart is empty or stock is insufficient.
     */
    public function placeOrder(User $user, array $validated): Order
    {
        $cartItems = Cart::with('product')
            ->where('user_id', $user->id)
            ->get();

        if ($cartItems->isEmpty()) {
            throw new OrderException('Cart is empty');
        }

        $outOfStock = $cartItems->first(function ($item) {
            return $item->product->stock < $item->qty;
        });

        if ($outOfStock) {
            throw new OrderException('"'.$outOfStock->product->name.'" does not have enough stock.');
        }

        $subtotal = $cartItems->sum(fn ($item) => $item->product->price * $item->qty);
        $total = $subtotal;
        $paymentMethod = PaymentMethod::from($validated['payment_method']);
        $status = $paymentMethod->initialStatus();

        return DB::transaction(function () use ($user, $cartItems, $subtotal, $total, $validated, $status) {
            foreach ($cartItems as $item) {
                $affected = Product::where('id', $item->product_id)
                    ->where('stock', '>=', $item->qty)
                    ->decrement('stock', $item->qty);

                if ($affected === 0) {
                    throw new OrderException('"'.$item->product->name.'" does not have enough stock.');
                }
            }

            $order = Order::create([
                'user_id' => $user->id,
                'subtotal' => $subtotal,
                'discount' => 0,
                'total' => $total,
                'status' => $status,
                'payment_method' => $validated['payment_method'],
                'shipping_name' => $validated['shipping_name'],
                'shipping_email' => $validated['shipping_email'],
                'shipping_phone' => $validated['shipping_phone'],
                'shipping_address' => $validated['shipping_address'],
                'shipping_city' => $validated['shipping_city'],
                'shipping_postal_code' => $validated['shipping_postal_code'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->qty,
                    'price' => $item->product->price,
                ]);
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'status' => $status,
                'note' => $status === 'confirmed'
                    ? 'Order placed (Cash on Delivery)'
                    : 'Order placed',
            ]);

            Cart::where('user_id', $user->id)->delete();

            return $order;
        });
    }
}
