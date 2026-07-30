<?php

namespace App\Ai\Tools;

use App\Models\Order;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetOrderStatus implements Tool
{
    public function description(): Stringable|string
    {
        return 'Look up the status and details of an order by its ID. Returns order status, items, total, and shipping info.';
    }

    public function handle(Request $request): Stringable|string
    {
        $order = Order::with(['items.product', 'statuses' => function ($q) {
            $q->latest()->limit(5);
        }])->find($request['order_id']);

        if (! $order) {
            return 'No order found with ID '.$request['order_id'].'.';
        }

        $items = $order->items->map(fn ($item) => [
            'product' => $item->product?->name ?? 'Unknown',
            'quantity' => $item->quantity,
            'price' => $item->price,
        ]);

        $statusHistory = $order->statuses->map(fn ($s) => [
            'status' => $s->status,
            'date' => $s->created_at->format('M d, Y g:i A'),
            'note' => $s->note,
        ]);

        return json_encode([
            'id' => $order->id,
            'status' => $order->status,
            'total' => $order->total,
            'items' => $items,
            'shipping_name' => $order->shipping_name,
            'shipping_address' => $order->shipping_address.', '.$order->shipping_city,
            'payment_method' => $order->payment_method,
            'status_history' => $statusHistory,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'order_id' => $schema->integer()->description('The ID of the order to look up')->required(),
        ];
    }
}
