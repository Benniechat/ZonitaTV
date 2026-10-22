<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Channel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'tvg_id',
        'description',
        'tagline',
        'logo_path',
        'banner_path',
        'broadcaster_name',
        'broadcaster_website',
        'official_destination_url',
        'country_code',
        'language_code',
        'playback_mode',
        'curation_type',
        'curation_keywords',
        'fallback_video_id',
        'is_featured',
        'is_active',
        'is_published',
        'display_order',
        'last_verified_at',
        'is_technically_healthy',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'is_published' => 'boolean',
        'is_technically_healthy' => 'boolean',
        'curation_keywords' => 'array',
        'last_verified_at' => 'datetime',
        'display_order' => 'integer',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'channel_categories');
    }

    public function streams(): HasMany
    {
        return $this->hasMany(ChannelStream::class);
    }

    public function primaryStream(): HasOne
    {
        return $this->hasOne(ChannelStream::class)
            ->where('is_active', true)
            ->where('is_backup', false)
            ->whereNotNull('stream_url')
            ->where('stream_url', '!=', '')
            ->latest('id');
    }

    public function contentRights(): HasMany
    {
        return $this->hasMany(ContentRight::class);
    }

    public function latestRights(): HasOne
    {
        return $this->hasOne(ContentRight::class)->latestOfMany();
    }

    public function programmes(): HasMany
    {
        return $this->hasMany(Programme::class);
    }

    public function streamChecks(): HasMany
    {
        return $this->hasMany(StreamCheck::class);
    }

    public function viewingEvents(): HasMany
    {
        return $this->hasMany(ViewingEvent::class);
    }

    public function problemReports(): HasMany
    {
        return $this->hasMany(ProblemReport::class);
    }

    public function favourites(): HasMany
    {
        return $this->hasMany(Favourite::class);
    }

    public function sourceRule(): HasOne
    {
        return $this->hasOne(ChannelSourceRule::class);
    }

    public function scheduleEntries(): HasMany
    {
        return $this->hasMany(ScheduleEntry::class);
    }

    public function playbackFailures(): HasMany
    {
        return $this->hasMany(PlaybackFailure::class);
    }

    public function channelVideos(): HasMany
    {
        return $this->hasMany(ChannelVideo::class);
    }

    public function broadcastHistory(): HasMany
    {
        return $this->hasMany(BroadcastHistory::class);
    }

    public function scheduleBlocks(): HasMany
    {
        return $this->hasMany(ScheduleBlock::class);
    }

    public function systemAlerts(): HasMany
    {
        return $this->hasMany(SystemAlert::class);
    }
}
