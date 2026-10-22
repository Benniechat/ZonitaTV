<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentRight extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel_id',
        'rights_holder_name',
        'contract_reference',
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
        return $this->expires_at && $this->expires_at->isPast();
    }
}
