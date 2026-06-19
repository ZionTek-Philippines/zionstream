<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stream extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'channel_id',
        'title',
        'description',
        'thumbnail',
        'agora_channel_name',
        'agora_uid',
        'status',
        'claim_keywords',
        'peak_viewer_count',
        'scheduled_at',
        'started_at',
        'ended_at',
    ];

    protected $attributes = [
        'claim_keywords' => '["mine"]',
    ];

    protected function casts(): array
    {
        return [
            'claim_keywords'     => 'array',
            'peak_viewer_count'  => 'integer',
            'scheduled_at'       => 'datetime',
            'started_at'         => 'datetime',
            'ended_at'           => 'datetime',
        ];
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function recordings(): HasMany
    {
        return $this->hasMany(StreamRecording::class);
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(StreamChatMessage::class);
    }

    public function streamProducts(): HasMany
    {
        return $this->hasMany(StreamProduct::class);
    }

    public function activeStreamProduct(): HasOne
    {
        return $this->hasOne(StreamProduct::class)->where('is_active', true);
    }

    public function productClaims(): HasMany
    {
        return $this->hasMany(ProductClaim::class);
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'stream_category');
    }
}
