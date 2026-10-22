<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChannelStream extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel_id',
        'label',
        'stream_type',
        'stream_url',
        'embed_code',
        'is_backup',
        'is_active',
        'bitrate_kbps',
    ];

    protected $casts = [
        'is_backup' => 'boolean',
        'is_active' => 'boolean',
        'bitrate_kbps' => 'integer',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function streamChecks(): HasMany
    {
        return $this->hasMany(StreamCheck::class);
    }
}
