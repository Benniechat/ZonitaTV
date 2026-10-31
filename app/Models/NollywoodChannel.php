<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NollywoodChannel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'channel_category',
        'creator_or_lead',
        'country_code',
        'primary_language',
        'sub_genres',
        'youtube_channel_name',
        'youtube_channel_url',
        'youtube_handle',
        'website_url',
        'contact_email',
        'contact_phone',
        'catalogue_size_est',
        'has_full_length_movies',
        'lead_priority',
        'sample_feature_titles',
        'licensing_status',
        'approved_films_count',
        'commercial_use_approved',
        'continuous_24_7_approved',
        'permitted_territories',
        'notes',
        'last_scanned_at',
    ];

    protected $casts = [
        'sub_genres' => 'array',
        'sample_feature_titles' => 'array',
        'permitted_territories' => 'array',
        'has_full_length_movies' => 'boolean',
        'commercial_use_approved' => 'boolean',
        'continuous_24_7_approved' => 'boolean',
        'catalogue_size_est' => 'integer',
        'approved_films_count' => 'integer',
        'lead_priority' => 'integer',
        'last_scanned_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->slug) && ! empty($model->name)) {
                $model->slug = Str::slug($model->name);
            }
            if (empty($model->youtube_channel_name) && ! empty($model->name)) {
                $model->youtube_channel_name = $model->name;
            }
            if (empty($model->youtube_channel_url) && ! empty($model->slug)) {
                $model->youtube_channel_url = 'https://www.youtube.com/@'.$model->slug;
            }
        });
    }

    public function scopeTopPriority(Builder $query): Builder
    {
        return $query->where('lead_priority', 1);
    }

    public function scopeFullLength(Builder $query): Builder
    {
        return $query->where('has_full_length_movies', true);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->whereIn('licensing_status', ['agreement_signed', 'active_broadcasting']);
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->channel_category) {
            'popular_nollywood' => 'Contemporary Nollywood',
            'yoruba_movies' => 'Yoruba Cinema',
            'aggregator_library' => 'Movie Library & Aggregator',
            'producer_studio' => 'Studio & Entertainment Network',
            default => 'Nollywood Cinema',
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->licensing_status) {
            'active_broadcasting' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
            'agreement_signed' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
            'syndication_proposed' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
            'contact_initiated' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
            'declined' => 'bg-red-500/10 text-red-400 border-red-500/20',
            default => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
        };
    }

    public function getFormattedStatusAttribute(): string
    {
        return match ($this->licensing_status) {
            'active_broadcasting' => 'Active 24/7 Linear',
            'agreement_signed' => 'Licensed Partner',
            'syndication_proposed' => 'Proposal Submitted',
            'contact_initiated' => 'Outreach Initiated',
            'declined' => 'Not Participating',
            default => 'Prospect Identified',
        };
    }

    public function getPriorityBadgeColorAttribute(): string
    {
        return match ($this->lead_priority) {
            1 => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
            2 => 'bg-blue-500/20 text-blue-400 border-blue-500/30',
            default => 'bg-gray-800 text-gray-400 border-gray-700',
        };
    }
}
