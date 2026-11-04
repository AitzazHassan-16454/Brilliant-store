<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Exceptions\OrderException;
use App\Mail\OrderPlaced;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Services\OrderService;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CheckoutController extends Controller
{
    /**
     * Place an order from the current user's cart.
     */
    public function checkout(Request $request, OrderService $orderService, StripeService $stripeService): Response
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

        if ($validated['payment_method'] === PaymentMethod::Stripe->value) {
            try {
                $session = $stripeService->createCheckoutSession($order);
                $order->update(['stripe_session_id' => $session->id]);

                return Inertia::location($session->url);
            } catch (Throwable $e) {
                Log::error('Stripe session creation failed', ['error' => $e->getMessage()]);

                return redirect('/orders')
                    ->with('error', 'Order created, but unable to redirect to Stripe. You can retry payment later.')
                    ->with('tracking_code', $order->tracking_code);
            }
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

    /**
     * Handle return from a successful Stripe Checkout session.
     */
    public function stripeSuccess(Request $request, StripeService $stripeService)
    {
        $sessionId = $request->query('session_id');
        if (! $sessionId) {
            return redirect('/orders')->with('error', 'Missing Stripe session.');
        }

        try {
            $session = $stripeService->retrieveCheckoutSession($sessionId);
        } catch (Throwable $e) {
            Log::error('Failed to retrieve Stripe session', ['error' => $e->getMessage()]);

            return redirect('/orders')->with('error', 'Unable to verify Stripe payment.');
        }

        $orderId = $session->client_reference_id ?? ($session->metadata['order_id'] ?? null);
        $order = Order::where('id', $orderId)
            ->where('user_id', auth()->id())
            ->first();

        if (! $order) {
            $order = Order::where('stripe_session_id', $sessionId)
                ->where('user_id', auth()->id())
                ->firstOrFail();
        }

        if ($session->payment_status === 'paid' && $order->status !== 'confirmed') {
            $paymentIntentId = is_string($session->payment_intent)
                ? $session->payment_intent
                : ($session->payment_intent->id ?? null);

            $order->update([
                'status' => 'confirmed',
                'stripe_session_id' => $sessionId,
                'stripe_payment_intent_id' => $paymentIntentId,
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'status' => 'confirmed',
                'note' => 'Payment received via Stripe (Payment Intent: '.($paymentIntentId ?? 'N/A').')',
            ]);

            try {
                Mail::to($order->shipping_email)->send(new OrderPlaced($order->fresh()));
            } catch (Throwable $e) {
                Log::warning('Failed to send order confirmation email for order '.$order->id, ['error' => $e->getMessage()]);
            }
        }

        return redirect('/orders')
            ->with('success', 'Payment successful! Your order has been placed.')
            ->with('tracking_code', $order->tracking_code);
    }

    /**
     * Handle cancellation of a Stripe Checkout session.
     */
    public function stripeCancel(Order $order)
    {
        abort_if($order->user_id !== auth()->id(), 403);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'status' => $order->status,
            'note' => 'Stripe checkout cancelled by customer',
        ]);

        return redirect('/orders')
            ->with('error', 'Stripe payment was cancelled. Your order remains pending.')
            ->with('tracking_code', $order->tracking_code);
    }

    /**
     * Handle asynchronous Stripe webhooks.
     */
    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook');

        $event = null;

        if ($webhookSecret && $sigHeader) {
            try {
                $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
            } catch (Throwable $e) {
                Log::warning('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);

                return response()->json(['error' => 'Invalid signature'], 400);
            }
        } else {
            $data = json_decode($payload, true);
            if (! isset($data['type'])) {
                return response()->json(['error' => 'Invalid payload'], 400);
            }
            $event = $data;
        }

        $eventType = is_object($event) ? $event->type : $event['type'];

        if ($eventType === 'checkout.session.completed') {
            $session = is_object($event) ? $event->data->object : (object) $event['data']['object'];
            $metadata = is_object($session->metadata ?? null) ? (array) $session->metadata : ($session->metadata ?? []);
            $orderId = $session->client_reference_id ?? ($metadata['order_id'] ?? null);

            $order = Order::find($orderId);
            if (! $order && isset($session->id)) {
                $order = Order::where('stripe_session_id', $session->id)->first();
            }

            if ($order && $order->status !== 'confirmed') {
                $paymentIntent = $session->payment_intent ?? null;
                $paymentIntentId = is_string($paymentIntent) ? $paymentIntent : ($paymentIntent->id ?? null);

                $order->update([
                    'status' => 'confirmed',
                    'stripe_session_id' => $session->id,
                    'stripe_payment_intent_id' => $paymentIntentId,
                ]);

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'status' => 'confirmed',
                    'note' => 'Payment received via Stripe Webhook (Payment Intent: '.($paymentIntentId ?? 'N/A').')',
                ]);

                try {
                    Mail::to($order->shipping_email)->send(new OrderPlaced($order->fresh()));
                } catch (Throwable $e) {
                    Log::warning('Failed to send order confirmation email for order '.$order->id, ['error' => $e->getMessage()]);
                }
            }
        }

        return response()->json(['received' => true]);
    }
}
