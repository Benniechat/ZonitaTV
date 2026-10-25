<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        'video_id',
        'title',
        'original_title',
        'slug',
        'release_year',
        'genre',
        'language',
        'runtime_minutes',
        'duration_seconds',
        'synopsis',
        'poster_url',
        'backdrop_url',
        'source_provider',
        'source_url',
        'source_identifier',
        'stream_url',
        'media_format',
        'rights_classification',
        'licence_name',
        'licence_reference',
        'commercial_use_permitted',
        'redistribution_permitted',
        'public_performance_permitted',
        'permitted_territories',
        'blocked_territories',
        'attribution_requirements',
        'licence_expires_at',
        'playback_method',
        'verification_status',
        'last_verified_at',
        'is_schedulable',
        'target_channel_slug',
        'notes',
    ];

    protected $casts = [
        'release_year' => 'integer',
        'runtime_minutes' => 'integer',
        'duration_seconds' => 'integer',
        'commercial_use_permitted' => 'boolean',
        'redistribution_permitted' => 'boolean',
        'public_performance_permitted' => 'boolean',
        'is_schedulable' => 'boolean',
        'permitted_territories' => 'array',
        'blocked_territories' => 'array',
        'licence_expires_at' => 'datetime',
        'last_verified_at' => 'datetime',
    ];

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('verification_status', 'verified');
    }

    public function scopeSchedulable(Builder $query): Builder
    {
        return $query->where('is_schedulable', true)
            ->where('verification_status', 'verified');
    }

    public function scopePublicDomain(Builder $query): Builder
    {
        return $query->where('rights_classification', 'public_domain');
    }

    public function scopeCommercialApproved(Builder $query): Builder
    {
        return $query->where('commercial_use_permitted', true)
            ->where('redistribution_permitted', true)
            ->where('public_performance_permitted', true);
    }

    public function isLicenceActive(): bool
    {
        if ($this->rights_classification === 'public_domain') {
            return true;
        }

        if ($this->licence_expires_at && $this->licence_expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function getPosterUrlAttribute(): string
    {
        if (! empty($this->attributes['poster_url'])) {
            return $this->attributes['poster_url'];
        }

        if ($this->video && ! empty($this->video->thumbnail_url)) {
            return $this->video->thumbnail_url;
        }

        return asset('images/logo/zonitatvlogo2.png');
    }

    public function getRightsBadgeColorAttribute(): string
    {
        return match ($this->rights_classification) {
            'public_domain' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
            'creative_commons' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
            'authorized_distributor' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
            'direct_licence' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
            'under_review' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
            default => 'bg-red-500/10 text-red-400 border-red-500/20',
        };
    }

    public function getFormattedProviderNameAttribute(): string
    {
        return match ($this->source_provider) {
            'internet_archive' => 'Internet Archive (Archive.org)',
            'library_of_congress' => 'Library of Congress (National Screening Room)',
            'wikimedia_commons' => 'Wikimedia Commons',
            'youtube_official' => 'YouTube Verified Official Movie',
            'nollywood_distributor' => 'Authorized Nollywood Distributor',
            'independent_filmmaker' => 'Independent Filmmaker Agreement',
            default => ucfirst(str_replace('_', ' ', $this->source_provider)),
        };
    }
}
