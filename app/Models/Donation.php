<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donation extends Model
{
    protected $fillable = ['stream_id', 'user_id', 'amount', 'currency', 'message', 'status'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function stream(): BelongsTo { return $this->belongsTo(Stream::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
