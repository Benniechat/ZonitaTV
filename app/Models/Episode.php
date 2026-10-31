<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Episode extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'season_id',
        'episode_number',
        'title',
        'slug',
        'synopsis',
        'video_id',
        'stream_url',
        'thumbnail_url',
        'duration_seconds',
        'aspect_ratio',
        'resolution',
        'air_date',
    ];

    protected $casts = [
        'episode_number' => 'integer',
        'duration_seconds' => 'integer',
        'air_date' => 'date',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }
}
