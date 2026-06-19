<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StreamRecording extends Model
{
    use HasFactory;

    protected $fillable = [
        'stream_id',
        'title',
        'url',
        'duration_seconds',
        'file_size',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published'     => 'boolean',
            'duration_seconds' => 'integer',
            'file_size'        => 'integer',
        ];
    }

    public function stream(): BelongsTo
    {
        return $this->belongsTo(Stream::class);
    }
}
