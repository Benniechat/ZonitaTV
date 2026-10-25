<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IslamicContentProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'provider_type',
        'content_categories',
        'country_code',
        'headquarters_city',
        'primary_language',
        'supported_subtitles',
        'youtube_channel_name',
        'youtube_channel_url',
        'youtube_handle',
        'official_website',
        'contact_email',
        'contact_phone',
        'catalogue_size_est',
        'recent_upload_activity',
        'sample_titles',
        'source_classification',
        'verification_status',
        'confidence_score',
        'licensing_status',
        'commercial_use_permitted',
        'continuous_24_7_permitted',
        'permitted_territories',
        'target_channels',
        'notes',
        'last_verified_at',
    ];

    protected $casts = [
        'content_categories' => 'array',
        'supported_subtitles' => 'array',
        'sample_titles' => 'array',
        'permitted_territories' => 'array',
        'target_channels' => 'array',
        'commercial_use_permitted' => 'boolean',
        'continuous_24_7_permitted' => 'boolean',
        'catalogue_size_est' => 'integer',
        'confidence_score' => 'integer',
        'last_verified_at' => 'datetime',
    ];

    public function licensingRequests(): HasMany
    {
        return $this->hasMany(IslamicLicensingRequest::class, 'provider_id');
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->whereIn('verification_status', ['VERIFIED_OWNER', 'AUTHORIZED_PARTNER']);
    }

    public function scopeApprovedForPlayout(Builder $query): Builder
    {
        return $query->whereIn('source_classification', [
            'OFFICIAL_CONTENT_OWNER',
            'AUTHORIZED_DISTRIBUTOR',
            'LICENSED_STREAMING_PROVIDER',
            'OPENLY_LICENSED_SOURCE',
            'OFFICIAL_EMBED_ONLY',
        ])->whereIn('licensing_status', ['AGREEMENT_SIGNED', 'ACTIVE_BROADCASTING']);
    }

    public function scopeByChannel(Builder $query, string $channelSlug): Builder
    {
        return $query->whereJsonContains('target_channels', $channelSlug);
    }

    public function getClassificationBadgeColorAttribute(): string
    {
        return match ($this->source_classification) {
            'OFFICIAL_CONTENT_OWNER' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
            'AUTHORIZED_DISTRIBUTOR' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
            'LICENSED_STREAMING_PROVIDER' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
            'OPENLY_LICENSED_SOURCE' => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20',
            'OFFICIAL_EMBED_ONLY' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
            'DISCOVERY_ONLY' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
            default => 'bg-gray-800 text-gray-400 border-gray-700',
        };
    }

    public function getLicensingBadgeColorAttribute(): string
    {
        return match ($this->licensing_status) {
            'ACTIVE_BROADCASTING' => 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30',
            'AGREEMENT_SIGNED' => 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30',
            'NEGOTIATING' => 'bg-purple-500/15 text-purple-300 border-purple-500/30',
            'OUTREACH_INITIATED' => 'bg-blue-500/15 text-blue-300 border-blue-500/30',
            'DECLINED' => 'bg-red-500/15 text-red-300 border-red-500/30',
            default => 'bg-amber-500/15 text-amber-300 border-amber-500/30',
        };
    }

    public function getFormattedProviderTypeAttribute(): string
    {
        return match ($this->provider_type) {
            'production_company' => 'Film & Drama Production Company',
            'television_network' => 'Television & Broadcast Network',
            'independent_filmmaker' => 'Independent Filmmaker',
            'educational_institute' => 'Islamic Educational Institute',
            'individual_scholar' => 'Islamic Scholar & Educator',
            'devotional_nasheed' => 'Devotional Nasheed & Arts',
            'quran_broadcaster' => 'Qur\'an Recitation Broadcaster',
            default => ucfirst(str_replace('_', ' ', $this->provider_type)),
        };
    }
}
