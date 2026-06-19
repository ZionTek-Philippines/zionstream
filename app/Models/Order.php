<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = ['order_number', 'user_id', 'stream_id', 'claim_id', 'total_amount', 'status', 'notes'];

    protected function casts(): array
    {
        return ['total_amount' => 'decimal:2'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function stream(): BelongsTo { return $this->belongsTo(Stream::class); }
    public function claim(): BelongsTo { return $this->belongsTo(ProductClaim::class, 'claim_id'); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function address(): HasOne { return $this->hasOne(OrderAddress::class); }
}
