<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StreamProduct extends Model
{
    protected $fillable = [
        'stream_id',
        'product_id',
        'is_active',
        'featured_price',
        'display_order',
        'activated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active'     => 'boolean',
            'featured_price'=> 'decimal:2',
            'display_order' => 'integer',
            'activated_at'  => 'datetime',
        ];
    }

    public function stream(): BelongsTo
    {
        return $this->belongsTo(Stream::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(ProductClaim::class);
    }

    public function effectivePrice(): string
    {
        return $this->featured_price ?? $this->product->price;
    }
}
