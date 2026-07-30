<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Subcategory extends Model
{
    use HasFactory;

    protected $fillable = ['uid', 'category_id', 'name'];

    public function getRouteKeyName(): string
    {
        return 'uid';
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Subcategory $subcategory) {
            if (empty($subcategory->uid)) {
                $subcategory->uid = 'SUB_'.strtoupper(Str::random(8));
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
