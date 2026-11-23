<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSetting extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'site_name',
        'tagline',
        'logo',
        'primary_color',
        'accent_color',
        'contact_email',
        'contact_hours',
        'footer_description',
    ];

    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'site_name' => 'GharBazaar',
            'tagline' => 'Shop Smart • Live Better',
            'logo' => null,
            'primary_color' => '#D4AF37',
            'accent_color' => '#B8960F',
            'contact_email' => 'info@gharbazaar.com',
            'contact_hours' => 'Mon - Fri: 9:00 AM - 6:00 PM',
            'footer_description' => 'GharBazaar — Shop Smart, Live Better. Everyday essentials and curated collections delivered to your doorstep.',
        ];
    }

    public static function current(): self
    {
        return static::query()->first() ?? new static(static::defaults());
    }

    /**
     * @return array<string, mixed>
     */
    public static function shared(): array
    {
        $settings = static::current();

        $logoUrl = null;
        if ($settings->logo && $settings->logo !== 'branding/logo.png' && file_exists(storage_path('app/public/'.$settings->logo))) {
            $logoUrl = asset('storage/'.$settings->logo);
        } elseif (file_exists(public_path('images/logo.png'))) {
            $logoUrl = asset('images/logo.png');
        } elseif ($settings->logo) {
            $logoUrl = asset('images/logo.png');
        }

        return [
            'site_name' => $settings->site_name,
            'tagline' => $settings->tagline,
            'logo_url' => $logoUrl,
            'primary_color' => $settings->primary_color,
            'accent_color' => $settings->accent_color,
            'contact_email' => $settings->contact_email,
            'contact_hours' => $settings->contact_hours,
            'footer_description' => $settings->footer_description,
        ];
    }

    public function getPrimaryColorAttribute(mixed $value): string
    {
        return $this->validColor($value, '#D4AF37');
    }

    public function getAccentColorAttribute(mixed $value): string
    {
        return $this->validColor($value, '#B8960F');
    }

    private function validColor(mixed $value, string $fallback): string
    {
        return is_string($value) && preg_match('/^#[0-9A-Fa-f]{6}$/', $value)
            ? $value
            : $fallback;
    }
}
