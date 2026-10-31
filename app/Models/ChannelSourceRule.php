<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChannelSourceRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel_id',
        'search_keywords',
        'excluded_keywords',
        'approved_publishers',
        'preferred_formats',
        'min_duration_minutes',
        'max_duration_minutes',
        'preferred_language',
        'repeat_cooldown_hours',
        'min_reserve_count',
        'allow_evergreen',
        'scheduling_mode',
        'priority_level',
        'interstitial_ident_interval',
        'timezone',
    ];

    protected $casts = [
        'search_keywords' => 'array',
        'excluded_keywords' => 'array',
        'approved_publishers' => 'array',
        'preferred_formats' => 'array',
        'allow_evergreen' => 'boolean',
        'min_duration_minutes' => 'integer',
        'max_duration_minutes' => 'integer',
        'repeat_cooldown_hours' => 'integer',
        'min_reserve_count' => 'integer',
        'interstitial_ident_interval' => 'integer',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }
}
