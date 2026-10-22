<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StreamCheck extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'channel_id',
        'channel_stream_id',
        'http_status_code',
        'response_time_ms',
        'is_reachable',
        'error_message',
        'checked_at',
    ];

    protected $casts = [
        'is_reachable' => 'boolean',
        'checked_at' => 'datetime',
        'http_status_code' => 'integer',
        'response_time_ms' => 'integer',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function stream(): BelongsTo
    {
        return $this->belongsTo(ChannelStream::class, 'channel_stream_id');
    }
}
