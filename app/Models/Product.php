<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sku',
        'name',
        'slug',
        'description',
        'price',
        'images',
        'stock_quantity',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price'          => 'decimal:2',
            'images'         => 'array',
            'stock_quantity' => 'integer',
            'is_active'      => 'boolean',
        ];
    }

    public function streamProducts(): HasMany
    {
        return $this->hasMany(StreamProduct::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(ProductClaim::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
