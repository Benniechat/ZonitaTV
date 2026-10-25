<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComedyChannel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'channel_category',
        'creator_or_brand',
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
        'has_full_length_episodes',
        'lead_priority',
        'sample_skit_titles',
        'licensing_status',
        'approved_items_count',
        'commercial_use_approved',
        'continuous_24_7_approved',
        'permitted_territories',
        'notes',
        'last_scanned_at',
    ];

    protected $casts = [
        'sub_genres' => 'array',
        'sample_skit_titles' => 'array',
        'permitted_territories' => 'array',
        'has_full_length_episodes' => 'boolean',
        'commercial_use_approved' => 'boolean',
        'continuous_24_7_approved' => 'boolean',
        'catalogue_size_est' => 'integer',
        'approved_items_count' => 'integer',
        'lead_priority' => 'integer',
        'last_scanned_at' => 'datetime',
    ];

    public function scopeTopPriority(Builder $query): Builder
    {
        return $query->where('lead_priority', 1);
    }

    public function scopeStandUp(Builder $query): Builder
    {
        return $query->where('channel_category', 'standup_comedy');
    }

    public function scopeSkitCreators(Builder $query): Builder
    {
        return $query->where('channel_category', 'skit_creators');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->whereIn('licensing_status', ['agreement_signed', 'active_broadcasting']);
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->channel_category) {
            'standup_comedy' => 'Stand-Up & Specials',
            'skit_creators' => 'Comedy Skits & Creators',
            'production_studios' => 'Comedy Studios & Sitcoms',
            'animated_comedy' => 'Animated Comedy & Toons',
            default => 'Comedy Entertainment',
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->licensing_status) {
            'active_broadcasting' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
            'agreement_signed' => 'bg-blue-500/20 text-blue-400 border-blue-500/30',
            'syndication_proposed' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
            'contact_initiated' => 'bg-purple-500/20 text-purple-400 border-purple-500/30',
            'prospect_identified' => 'bg-gray-500/20 text-gray-400 border-gray-500/30',
            default => 'bg-gray-500/20 text-gray-400 border-gray-500/30',
        };
    }

    public function getFormattedStatusAttribute(): string
    {
        return match ($this->licensing_status) {
            'active_broadcasting' => 'Broadcasting 24/7',
            'agreement_signed' => 'Licence Signed',
            'syndication_proposed' => 'Syndication Proposed',
            'contact_initiated' => 'Contact Initiated',
            'prospect_identified' => 'Vetted Prospect',
            default => ucfirst(str_replace('_', ' ', (string) $this->licensing_status)),
        };
    }

    public function getPriorityBadgeColorAttribute(): string
    {
        return match ($this->lead_priority) {
            1 => 'bg-red-500/20 text-red-400 border-red-500/30',
            2 => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
            default => 'bg-gray-500/20 text-gray-400 border-gray-500/30',
        };
    }
}
