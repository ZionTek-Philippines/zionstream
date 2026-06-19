<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChannelFollow extends Model
{
    protected $fillable = ['channel_id', 'user_id'];

    public function channel(): BelongsTo { return $this->belongsTo(Channel::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
