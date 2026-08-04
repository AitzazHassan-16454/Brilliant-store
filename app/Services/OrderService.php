<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Exceptions\OrderException;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderCoupon;
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
     * Validates stock and coupon server-side, then persists the order,
     * its items, coupon usage and status history inside a transaction.
     *
     * @param  array<string, mixed>  $validated
     *
     * @throws OrderException When the cart is empty, stock is insufficient
     *                        or the coupon is invalid.
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
        $discount = 0;
        $coupon = null;

        if ($validated['coupon_code'] ?? null) {
            $coupon = Coupon::where('code', strtoupper($validated['coupon_code']))->first();

            if (! $coupon || ! $coupon->isValid()) {
                throw new OrderException('Invalid or expired coupon code.');
            }

            if ($subtotal < $coupon->min_order) {
                throw new OrderException('Minimum order of $'.$coupon->min_order.' required for this coupon.');
            }

            $discount = $coupon->calculateDiscount($subtotal);
        }

        $total = max(0, $subtotal - $discount);
        $paymentMethod = PaymentMethod::from($validated['payment_method']);
        $status = $paymentMethod->initialStatus();

        return DB::transaction(function () use ($user, $cartItems, $subtotal, $discount, $total, $coupon, $validated, $status) {
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
                'discount' => $discount,
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

            if ($coupon) {
                OrderCoupon::create([
                    'order_id' => $order->id,
                    'coupon_id' => $coupon->id,
                    'discount_amount' => $discount,
                ]);

                $coupon->increment('used_count');
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
