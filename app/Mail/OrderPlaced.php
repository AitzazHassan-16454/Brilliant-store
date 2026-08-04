<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderPlaced extends Mailable
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order confirmed #'.$this->order->tracking_code.' — '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.placed',
            with: [
                'order' => $this->order,
                'trackingUrl' => route('track-order', ['code' => $this->order->tracking_code]),
            ],
        );
    }
}
