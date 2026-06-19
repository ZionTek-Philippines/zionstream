<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StreamChatMessage extends Model
{
    protected $fillable = [
        'stream_id',
        'user_id',
        'message',
        'type',
    ];

    public function stream(): BelongsTo
    {
        return $this->belongsTo(Stream::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function claim(): HasOne
    {
        return $this->hasOne(ProductClaim::class, 'chat_message_id');
    }
}
