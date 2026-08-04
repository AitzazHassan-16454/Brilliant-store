<x-mail::message>
# Order confirmed, {{ $order->shipping_name }}!

Thank you for your order at {{ config('app.name') }}. Your order has been placed successfully.

**Order #{{ $order->tracking_code }}**

<x-mail::button :url="$trackingUrl">
Track your order
</x-mail::button>

## Your items

<x-mail::table>
| Product | Qty | Price |
| :--- | :--- | ---: |
@foreach ($order->items as $item)
| {{ $item->product->name }} | {{ $item->quantity }} | ${{ $item->price }} |
@endforeach
</x-mail::table>

**Subtotal:** ${{ $order->subtotal }}
@if ($order->discount > 0)
**Discount:** -${{ $order->discount }}
@endif
**Total:** ${{ $order->total }}

## Shipping

{{ $order->shipping_name }}<br>
{{ $order->shipping_address }}{{ $order->shipping_city ? ', '.$order->shipping_city : '' }}{{ $order->shipping_postal_code ? ' '.$order->shipping_postal_code : '' }}

@php($paymentMethod = \App\Enums\PaymentMethod::tryFrom($order->payment_method))
@if ($paymentMethod)
## Payment method

**{{ $paymentMethod->label() }}** — {{ $paymentMethod->description() }}
@endif

@if ($order->notes)
**Order notes:** {{ $order->notes }}
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
