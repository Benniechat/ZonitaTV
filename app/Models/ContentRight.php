<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentRight extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel_id',
        'video_id',
        'movie_id',
        'series_id',
        'rights_holder_name',
        'contract_reference',
        'classification',
        'licence_name',
        'licensing_agreement_text',
        'attribution_requirements',
        'commercial_terms',
        'is_exclusive',
        'auto_delist_on_expiry',
        'status',
        'allow_directory_listing',
        'allow_web_embedding',
        'allow_direct_hls',
        'allow_mobile_apps',
        'allow_smart_tv',
        'allow_monetisation',
        'permitted_territories',
        'blocked_territories',
        'effective_from',
        'expires_at',
        'reviewed_by_user_id',
        'reviewed_at',
        'reviewer_notes',
    ];

    protected $casts = [
        'allow_directory_listing' => 'boolean',
        'allow_web_embedding' => 'boolean',
        'allow_direct_hls' => 'boolean',
        'allow_mobile_apps' => 'boolean',
        'allow_smart_tv' => 'boolean',
        'allow_monetisation' => 'boolean',
        'is_exclusive' => 'boolean',
        'auto_delist_on_expiry' => 'boolean',
        'commercial_terms' => 'array',
        'permitted_territories' => 'array',
        'blocked_territories' => 'array',
        'effective_from' => 'date',
        'expires_at' => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(RightsDocument::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && Carbon::parse($this->expires_at)->isPast();
    }

    public function isExpiringSoon(int $days = 7): bool
    {
        if (! $this->expires_at) {
            return false;
        }

        $expiry = Carbon::parse($this->expires_at);

        return $expiry->isFuture() && $expiry->diffInDays(now()) <= $days;
    }

    public function isTerritoryPermitted(?string $countryCode): bool
    {
        $code = strtoupper(trim((string) $countryCode));

        // 1. Check blocked territories first
        if (! empty($this->blocked_territories) && is_array($this->blocked_territories)) {
            if (in_array($code, array_map('strtoupper', $this->blocked_territories))) {
                return false;
            }
        }

        // 2. Check permitted territories (empty or GLOBAL means unrestricted)
        if (empty($this->permitted_territories) || ! is_array($this->permitted_territories)) {
            return true;
        }

        $permitted = array_map('strtoupper', $this->permitted_territories);
        if (in_array('GLOBAL', $permitted) || in_array('ALL', $permitted)) {
            return true;
        }

        return in_array($code, $permitted);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['directory_approved', 'embedding_approved', 'redistribution_approved'])
            ->where(function (Builder $q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeExpiringSoon(Builder $query, int $days = 7): Builder
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '>', now())
            ->where('expires_at', '<=', now()->addDays($days));
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', 'expired')
            ->orWhere(function (Builder $q) {
                $q->whereNotNull('expires_at')
                    ->where('expires_at', '<=', now());
            });
    }
}
