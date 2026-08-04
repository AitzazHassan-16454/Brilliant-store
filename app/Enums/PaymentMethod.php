<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case BankTransfer = 'bank_transfer';
    case CashOnDelivery = 'cod';
    case WhatsApp = 'whatsapp';

    public function label(): string
    {
        return match ($this) {
            self::BankTransfer => 'Bank Transfer',
            self::CashOnDelivery => 'Cash on Delivery',
            self::WhatsApp => 'Pay via WhatsApp',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::BankTransfer => 'Pay by bank transfer and share your proof to confirm the order.',
            self::CashOnDelivery => 'Pay in cash when your order is delivered.',
            self::WhatsApp => 'Pay through WhatsApp and confirm with our team.',
        };
    }

    public function initialStatus(): string
    {
        return match ($this) {
            self::CashOnDelivery => 'confirmed',
            default => 'pending',
        };
    }
}
