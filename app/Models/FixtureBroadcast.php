<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FixtureBroadcast extends Model
{
    use HasFactory;

    protected $fillable = [
        'fixture_id',
        'broadcaster_name',
        'source_url',
        'feed_endpoint',
        'operating_mode',
        'verification_status',
        'territory',
        'allow_embedding',
        'allow_redistribution',
        'rights_reference',
        'verified_at',
        'expires_at',
        'notes',
    ];

    protected $casts = [
        'allow_embedding' => 'boolean',
        'allow_redistribution' => 'boolean',
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function fixture(): BelongsTo
    {
        return $this->belongsTo(SportsFixture::class, 'fixture_id');
    }

    /**
     * Check if broadcast is confirmed for display.
     */
    public function isConfirmed(): bool
    {
        return in_array($this->verification_status, [
            'confirmed',
            'authorised_for_distribution',
        ]);
    }

    /**
     * Check if rebroadcasting (Mode C) is strictly authorised.
     */
    public function isAuthorisedForRebroadcasting(): bool
    {
        return $this->allow_redistribution && $this->verification_status === 'authorised_for_distribution';
    }

    /**
     * Check if embedding (Mode B) is authorised.
     */
    public function isEmbedPermitted(): bool
    {
        return $this->allow_embedding && in_array($this->verification_status, [
            'confirmed',
            'authorised_for_distribution',
        ]);
    }

    /**
     * Resolve destination URL fallback.
     */
    public function getViewingUrlAttribute(): string
    {
        return $this->source_url ?: config('sports.sportytv.official_url', 'https://sportybet.com/tv');
    }
}
