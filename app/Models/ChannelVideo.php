<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChannelVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel_id',
        'video_id',
        'relevance_score',
        'is_approved',
        'is_reserve',
        'broadcast_count',
        'last_broadcast_at',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'is_reserve' => 'boolean',
        'relevance_score' => 'integer',
        'broadcast_count' => 'integer',
        'last_broadcast_at' => 'datetime',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }
}
