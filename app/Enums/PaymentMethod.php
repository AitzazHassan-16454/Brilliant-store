<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CashOnDelivery = 'cod';
    case WhatsApp = 'whatsapp';

    public function label(): string
    {
        return match ($this) {
            self::CashOnDelivery => 'Cash on Delivery',
            self::WhatsApp => 'Payment Via WhatsApp',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::CashOnDelivery => 'Pay with cash directly when your order is delivered.',
            self::WhatsApp => 'Pay through WhatsApp and confirm with our team.',
        };
    }

    public function initialStatus(): string
    {
        return match ($this) {
            self::CashOnDelivery => 'confirmed',
            self::WhatsApp => 'pending',
        };
    }
}
