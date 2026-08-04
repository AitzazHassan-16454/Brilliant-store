<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderStatusUpdated extends Mailable
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order #'.$this->order->tracking_code.' is now '.$this->order->status,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.status-updated',
            with: [
                'order' => $this->order,
                'trackingUrl' => route('track-order', ['code' => $this->order->tracking_code]),
            ],
        );
    }
}
