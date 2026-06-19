<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductClaim extends Model
{
    protected $fillable = [
        'stream_id',
        'stream_product_id',
        'product_id',
        'user_id',
        'chat_message_id',
        'quantity',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function stream(): BelongsTo
    {
        return $this->belongsTo(Stream::class);
    }

    public function streamProduct(): BelongsTo
    {
        return $this->belongsTo(StreamProduct::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chatMessage(): BelongsTo
    {
        return $this->belongsTo(StreamChatMessage::class, 'chat_message_id');
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class, 'claim_id');
    }
}
