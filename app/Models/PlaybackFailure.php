<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaybackFailure extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'video_id',
        'channel_id',
        'error_code',
        'session_id',
        'user_agent',
        'ip_country',
        'recovered_with_video_id',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function replacementVideo(): BelongsTo
    {
        return $this->belongsTo(Video::class, 'recovered_with_video_id');
    }
}
