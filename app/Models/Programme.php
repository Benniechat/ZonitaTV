<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Programme extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel_id',
        'category_id',
        'title',
        'slug',
        'description',
        'presenter_or_artist',
        'poster_image',
        'video_source',
        'video_id',
        'video_url',
        'start_time',
        'end_time',
        'duration_minutes',
        'play_order',
        'is_live',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration_minutes' => 'integer',
        'play_order' => 'integer',
        'is_live' => 'boolean',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
