<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomOrder extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'description',
        'reference_image',
        'budget_min',
        'budget_max',
        'desired_date',
        'status',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'budget_min' => 'decimal:2',
            'budget_max' => 'decimal:2',
            'desired_date' => 'date',
            'status' => 'string',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
