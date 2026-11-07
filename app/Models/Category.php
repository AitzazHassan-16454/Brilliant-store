<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['uid', 'name', 'description', 'image'];

    public function getRouteKeyName(): string
    {
        return 'uid';
    }

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        return $this->where('uid', $value)
            ->orWhere('id', is_numeric($value) ? (int) $value : 0)
            ->orWhereRaw('LOWER(REPLACE(name, " ", "-")) = ?', [strtolower($value)])
            ->orWhereRaw('LOWER(name) = ?', [str_replace('-', ' ', strtolower($value))])
            ->first() ?? abort(404);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Category $category) {
            if (empty($category->uid)) {
                $category->uid = 'CAT_'.strtoupper(Str::random(8));
            }
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function subcategories(): HasMany
    {
        return $this->hasMany(Subcategory::class);
    }
}
