<?php

use App\Mail\OrderPlaced;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\StripeService;
use Illuminate\Support\Facades\Mail;
use Stripe\Checkout\Session;

beforeEach(function () {
    Mail::fake();
});

it('creates a stripe checkout session and redirects to stripe', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 45.00, 'stock' => 5]);

    Cart::create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'qty' => 1,
    ]);

    $fakeSession = Session::constructFrom([
        'id' => 'cs_test_mock_123',
        'url' => 'https://checkout.stripe.com/c/pay/cs_test_mock_123',
    ]);

    $stripeService = Mockery::mock(StripeService::class);
    $stripeService->shouldReceive('createCheckoutSession')
        ->once()
        ->andReturn($fakeSession);
    $this->app->instance(StripeService::class, $stripeService);

    $response = $this->actingAs($user)
        ->withHeaders(['X-Inertia' => 'true'])
        ->post('/checkout', [
            'shipping_name' => 'Jane Doe',
            'shipping_email' => 'jane@example.com',
            'shipping_phone' => '03009876543',
            'shipping_address' => '456 Commercial Rd',
            'shipping_city' => 'Islamabad',
            'shipping_postal_code' => '44000',
            'notes' => 'Please call on arrival',
            'payment_method' => 'stripe',
        ]);

    $order = Order::where('user_id', $user->id)->firstOrFail();

    expect($order->payment_method)->toBe('stripe');
    expect($order->status)->toBe('pending');
    expect($order->stripe_session_id)->toBe('cs_test_mock_123');

    // Inertia external redirect sets header X-Inertia-Location and status 409
    $response->assertStatus(409);
    $response->assertHeader('X-Inertia-Location', 'https://checkout.stripe.com/c/pay/cs_test_mock_123');
});

it('confirms the order and sends confirmation email on stripe success return', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'payment_method' => 'stripe',
        'status' => 'pending',
        'stripe_session_id' => 'cs_test_mock_123',
        'shipping_email' => 'jane@example.com',
    ]);

    $fakeSession = Session::constructFrom([
        'id' => 'cs_test_mock_123',
        'payment_status' => 'paid',
        'payment_intent' => 'pi_test_intent_999',
        'client_reference_id' => (string) $order->id,
        'metadata' => ['order_id' => $order->id],
    ]);

    $stripeService = Mockery::mock(StripeService::class);
    $stripeService->shouldReceive('retrieveCheckoutSession')
        ->with('cs_test_mock_123')
        ->once()
        ->andReturn($fakeSession);
    $this->app->instance(StripeService::class, $stripeService);

    $response = $this->actingAs($user)->get('/checkout/stripe/success?session_id=cs_test_mock_123');

    $response->assertRedirect('/orders');
    $response->assertSessionHas('success', 'Payment successful! Your order has been placed.');

    $order->refresh();
    expect($order->status)->toBe('confirmed');
    expect($order->stripe_payment_intent_id)->toBe('pi_test_intent_999');

    expect($order->statuses)->toHaveCount(1);
    expect($order->statuses->first()->status)->toBe('confirmed');
    expect($order->statuses->first()->note)->toContain('pi_test_intent_999');

    Mail::assertSent(OrderPlaced::class, function (OrderPlaced $mail) use ($order) {
        return $mail->order->is($order);
    });
});

it('records customer cancellation when returning from stripe cancel url', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'payment_method' => 'stripe',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($user)->get("/checkout/stripe/cancel/{$order->id}");

    $response->assertRedirect('/orders');
    $response->assertSessionHas('error', 'Stripe payment was cancelled. Your order remains pending.');

    $order->refresh();
    expect($order->status)->toBe('pending');
    expect($order->statuses->first()->note)->toBe('Stripe checkout cancelled by customer');
});

it('confirms order via stripe checkout.session.completed webhook', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'payment_method' => 'stripe',
        'status' => 'pending',
        'stripe_session_id' => 'cs_test_webhook_abc',
        'shipping_email' => 'jane@example.com',
    ]);

    $payload = [
        'type' => 'checkout.session.completed',
        'data' => [
            'object' => [
                'id' => 'cs_test_webhook_abc',
                'client_reference_id' => $order->id,
                'payment_intent' => 'pi_test_webhook_123',
                'metadata' => [
                    'order_id' => $order->id,
                ],
            ],
        ],
    ];

    $response = $this->postJson('/stripe/webhook', $payload);

    $response->assertOk();
    $response->assertJson(['received' => true]);

    $order->refresh();
    expect($order->status)->toBe('confirmed');
    expect($order->stripe_payment_intent_id)->toBe('pi_test_webhook_123');

    Mail::assertSent(OrderPlaced::class, function (OrderPlaced $mail) use ($order) {
        return $mail->order->is($order);
    });
});
