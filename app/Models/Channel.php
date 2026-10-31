<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Channel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'channel_number',
        'tvg_id',
        'description',
        'tagline',
        'logo_path',
        'banner_path',
        'brand_color',
        'watermark_logo_path',
        'broadcaster_name',
        'broadcaster_website',
        'content_partner_id',
        'official_destination_url',
        'country_code',
        'language_code',
        'playback_mode',
        'is_virtual',
        'curation_type',
        'curation_keywords',
        'fallback_video_id',
        'station_ident_video_id',
        'is_featured',
        'is_active',
        'is_published',
        'display_order',
        'last_verified_at',
        'is_technically_healthy',
    ];

    protected $casts = [
        'is_virtual' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'is_published' => 'boolean',
        'is_technically_healthy' => 'boolean',
        'curation_keywords' => 'array',
        'last_verified_at' => 'datetime',
        'display_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Channel $channel) {
            if (empty($channel->slug) && ! empty($channel->name)) {
                $channel->slug = Str::slug($channel->name);
            }
            if (empty($channel->official_destination_url)) {
                $slug = $channel->slug ?? Str::slug($channel->name);
                $channel->official_destination_url = "https://zonitatv.com/channel/{$slug}";
            }
        });
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'channel_categories');
    }

    /**
     * Resolve the channel logo URL, supporting Cloudinary CDN and local storage.
     */
    public function getLogoUrlAttribute(): string
    {
        if (empty($this->logo_path)) {
            return asset(config('channels.default_logo', 'images/logo/zonitatvlogo2.png'));
        }

        if (str_starts_with($this->logo_path, 'http://') || str_starts_with($this->logo_path, 'https://')) {
            return $this->logo_path;
        }

        return asset(ltrim($this->logo_path, '/'));
    }

    /**
     * Resolve the channel banner URL, supporting Cloudinary CDN, remote HTTP URLs, and category-themed broadcast backdrops.
     */
    public function getBannerUrlAttribute(): string
    {
        if (! empty($this->banner_path)) {
            if (str_starts_with($this->banner_path, 'http://') || str_starts_with($this->banner_path, 'https://')) {
                return $this->banner_path;
            }

            return asset(ltrim($this->banner_path, '/'));
        }

        return $this->getCategoryBannerUrl();
    }

    /**
     * Get a curated high-resolution broadcast banner based on channel category.
     */
    public function getCategoryBannerUrl(): string
    {
        $categorySlug = $this->categories->first()?->slug ?? 'default';
        $defaultBanners = config('channels.default_banners', []);

        return $defaultBanners[$categorySlug] ?? $defaultBanners['default'] ?? 'https://images.unsplash.com/photo-1526470608268-f674ce90ebd4?auto=format&fit=crop&w=1400&q=80';
    }

    /**
     * Get 2-letter uppercase initials for monogram badge fallback.
     */
    public function getInitialsAttribute(): string
    {
        $words = preg_split('/\s+/', trim($this->name));
        if (count($words) >= 2) {
            return mb_strtoupper(mb_substr($words[0], 0, 1).mb_substr($words[1], 0, 1));
        }

        return mb_strtoupper(mb_substr($this->name, 0, 2));
    }

    /**
     * Get the formatted channel dial number (e.g. "101").
     */
    public function getDisplayNumberAttribute(): string
    {
        return $this->channel_number ?: strval(100 + ($this->id ?? 0));
    }

    /**
     * Station accent brand color (fallback: default Zonita crimson #E50914).
     */
    public function getAccentColorAttribute(): string
    {
        return $this->brand_color ?: '#E50914';
    }

    /**
     * Resolve watermark logo URL.
     */
    public function getWatermarkUrlAttribute(): ?string
    {
        if (empty($this->watermark_logo_path)) {
            return $this->logo_url;
        }

        if (str_starts_with($this->watermark_logo_path, 'http://') || str_starts_with($this->watermark_logo_path, 'https://')) {
            return $this->watermark_logo_path;
        }

        return asset(ltrim($this->watermark_logo_path, '/'));
    }

    /**
     * Whether this channel is a 24/7 automated virtual channel.
     */
    public function isVirtual(): bool
    {
        return (bool) ($this->is_virtual || $this->playback_mode === 'zoniva_original' || $this->sourceRule()->exists());
    }

    public function scopeVirtual($query)
    {
        return $query->where(function ($q) {
            $q->where('is_virtual', true)
                ->orWhere('playback_mode', 'zoniva_original')
                ->orWhereHas('sourceRule');
        });
    }

    public function scopeOnAir($query)
    {
        return $query->where('is_active', true)->where('is_published', true);
    }

    public function fallbackVideo(): BelongsTo
    {
        return $this->belongsTo(Video::class, 'fallback_video_id');
    }

    public function stationIdentVideo(): BelongsTo
    {
        return $this->belongsTo(Video::class, 'station_ident_video_id');
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

    public function contentPartner(): BelongsTo
    {
        return $this->belongsTo(ContentPartner::class, 'content_partner_id');
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
