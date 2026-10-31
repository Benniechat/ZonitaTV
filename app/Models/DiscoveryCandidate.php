<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscoveryCandidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_provider_id',
        'channel_id',
        'category_id',
        'source_type',
        'source_url',
        'source_identifier',
        'content_hash',
        'title',
        'description',
        'creator_name',
        'thumbnail_url',
        'duration_seconds',
        'aspect_ratio',
        'resolution',
        'content_rating',
        'tags',
        'published_at',
        'rights_basis',
        'intake_status',
        'rejection_reason',
        'approved_video_id',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
        'tags' => 'array',
        'published_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(ContentProvider::class, 'content_provider_id');
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function approvedVideo(): BelongsTo
    {
        return $this->belongsTo(Video::class, 'approved_video_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopeStaged(Builder $query): Builder
    {
        return $query->where('intake_status', 'staged');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('intake_status', 'approved');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('intake_status', 'rejected');
    }
}
