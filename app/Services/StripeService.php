<?php

namespace App\Services;

use App\Models\Order;
use Stripe\Checkout\Session;
use Stripe\StripeClient;

class StripeService
{
    protected StripeClient $client;

    public function __construct(?StripeClient $client = null)
    {
        $this->client = $client ?? new StripeClient((string) config('services.stripe.secret'));
    }

    /**
     * Create a Stripe Checkout Session for an order.
     */
    public function createCheckoutSession(Order $order): Session
    {
        return $this->client->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'customer_email' => $order->shipping_email,
            'client_reference_id' => (string) $order->id,
            'metadata' => [
                'order_id' => (string) $order->id,
                'tracking_code' => $order->tracking_code,
            ],
            'line_items' => [[
                'price_data' => [
                    'currency' => config('services.stripe.currency', 'usd'),
                    'product_data' => [
                        'name' => 'Order #'.$order->tracking_code,
                        'description' => 'Payment for order #'.$order->tracking_code,
                    ],
                    'unit_amount' => (int) round($order->total * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('checkout.stripe.success', [], true).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.stripe.cancel', ['order' => $order->id], true),
        ]);
    }

    /**
     * Retrieve a Stripe Checkout Session by ID.
     */
    public function retrieveCheckoutSession(string $sessionId): Session
    {
        return $this->client->checkout->sessions->retrieve($sessionId, [
            'expand' => ['payment_intent'],
        ]);
    }

    /**
     * Get the underlying StripeClient instance.
     */
    public function getClient(): StripeClient
    {
        return $this->client;
    }
}
