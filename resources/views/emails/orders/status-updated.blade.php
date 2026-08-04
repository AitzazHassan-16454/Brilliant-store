<x-mail::message>
# Order status update

Hi {{ $order->shipping_name }},

The status of your order has changed.

**Order #{{ $order->tracking_code }}**

<x-mail::panel>
**New status:** {{ str($order->status)->headline() }}
</x-mail::panel>

<x-mail::button :url="$trackingUrl">
Track your order
</x-mail::button>

If you have any questions, just reply to this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
