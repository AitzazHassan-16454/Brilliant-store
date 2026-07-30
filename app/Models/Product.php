<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid',
        'name',
        'price',
        'stock',
        'is_active',
        'image',
        'description',
        'category_id',
        'subcategory_id',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uid';
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Product $product) {
            if (empty($product->uid)) {
                $product->uid = 'PRD_'.strtoupper(Str::random(8));
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function getAverageRatingAttribute(): ?float
    {
        $avg = $this->reviews()->where('status', 'approved')->avg('rating');

        return $avg ? round($avg, 1) : null;
    }

    public function getReviewsCountAttribute(): int
    {
        return $this->reviews()->where('status', 'approved')->count();
    }

    public function getIsInStockAttribute(): bool
    {
        return $this->stock > 0;
    }
}
