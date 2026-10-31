<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'youtube_id',
        'title',
        'description',
        'thumbnail_url',
        'media_url',
        'cloudinary_public_id',
        'channel_title',
        'content_partner_id',
        'published_at',
        'duration_seconds',
        'aspect_ratio',
        'resolution',
        'content_rating',
        'advisory_tags',
        'language_code',
        'country_code',
        'privacy_status',
        'is_embeddable',
        'availability_status',
        'confidence_score',
        'consecutive_failures',
        'cooldown_until',
        'rights_basis',
        'format_type',
        'category_slug',
        'is_evergreen',
        'relevance_score',
        'last_verified_at',
        'tags',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'last_verified_at' => 'datetime',
        'cooldown_until' => 'datetime',
        'is_embeddable' => 'boolean',
        'is_evergreen' => 'boolean',
        'duration_seconds' => 'integer',
        'confidence_score' => 'integer',
        'consecutive_failures' => 'integer',
        'relevance_score' => 'integer',
        'advisory_tags' => 'array',
        'tags' => 'array',
    ];

    public function scheduleEntries(): HasMany
    {
        return $this->hasMany(ScheduleEntry::class);
    }

    public function verificationLogs(): HasMany
    {
        return $this->hasMany(VideoVerificationLog::class);
    }

    public function playbackFailures(): HasMany
    {
        return $this->hasMany(PlaybackFailure::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(ContentComplaint::class);
    }

    public function contentPartner(): BelongsTo
    {
        return $this->belongsTo(ContentPartner::class, 'content_partner_id');
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class);
    }

    public function movies(): HasMany
    {
        return $this->hasMany(Movie::class);
    }

    public function programmes(): HasMany
    {
        return $this->hasMany(Programme::class, 'video_record_id');
    }

    public function scopeEligibleForScheduling(Builder $query): Builder
    {
        return $query->whereIn('availability_status', ['approved', 'scheduled'])
            ->whereNotIn('availability_status', ['disabled', 'removed', 'complaint_received', 'under_investigation', 'restricted', 'expired', 'rights_rejected'])
            ->where('is_embeddable', true)
            ->where('confidence_score', '>=', 50)
            ->where(function (Builder $q) {
                $q->whereNull('cooldown_until')
                    ->orWhere('cooldown_until', '<=', now());
            });
    }

    public function scopeEvergreen(Builder $query): Builder
    {
        return $query->where('is_evergreen', true);
    }

    public function isPlayable(): bool
    {
        if (! $this->is_embeddable) {
            return false;
        }

        if (in_array($this->availability_status, [
            'deleted_or_missing',
            'embedding_disabled',
            'private_or_restricted',
            'rights_rejected',
            'disabled',
            'removed',
            'complaint_received',
            'under_investigation',
            'restricted',
            'expired',
        ])) {
            return false;
        }

        if ($this->cooldown_until && $this->cooldown_until->isFuture()) {
            return false;
        }

        return $this->confidence_score >= 50;
    }

    /**
     * Get the display thumbnail URL, prioritizing Cloudinary CDN.
     */
    public function getThumbnailUrlAttribute(): string
    {
        if (! empty($this->attributes['thumbnail_url'])) {
            return $this->attributes['thumbnail_url'];
        }

        if (! empty($this->youtube_id)) {
            $cloudName = config('services.cloudinary.cloud_name');
            if ($cloudName) {
                return "https://res.cloudinary.com/{$cloudName}/image/fetch/f_auto,q_auto,w_480,h_270,c_fill/https://img.youtube.com/vi/{$this->youtube_id}/hqdefault.jpg";
            }

            return "https://img.youtube.com/vi/{$this->youtube_id}/hqdefault.jpg";
        }

        return asset('images/logo/zonitatvlogo2.png');
    }
}
