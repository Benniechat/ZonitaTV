<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SportsFixture extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_id',
        'home_team_id',
        'away_team_id',
        'provider',
        'provider_id',
        'utc_kickoff',
        'lagos_kickoff',
        'status',
        'matchday',
        'round',
        'home_score',
        'away_score',
        'match_clock',
        'venue',
        'is_featured',
        'is_live',
        'thumbnail_card_url',
        'last_synced_at',
        'raw_metadata',
    ];

    protected $casts = [
        'utc_kickoff' => 'datetime',
        'lagos_kickoff' => 'datetime',
        'last_synced_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_live' => 'boolean',
        'home_score' => 'integer',
        'away_score' => 'integer',
        'matchday' => 'integer',
        'raw_metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (SportsFixture $fixture) {
            if ($fixture->utc_kickoff && empty($fixture->lagos_kickoff)) {
                $fixture->lagos_kickoff = $fixture->utc_kickoff->copy()->timezone(config('sports.timezone', 'Africa/Lagos'));
            }
        });

        static::updating(function (SportsFixture $fixture) {
            if ($fixture->isDirty('utc_kickoff') && ! $fixture->isDirty('lagos_kickoff')) {
                $fixture->lagos_kickoff = $fixture->utc_kickoff->copy()->timezone(config('sports.timezone', 'Africa/Lagos'));
            }
        });
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(SportsCompetition::class, 'competition_id');
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(SportsTeam::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(SportsTeam::class, 'away_team_id');
    }

    public function broadcasts(): HasMany
    {
        return $this->hasMany(FixtureBroadcast::class, 'fixture_id');
    }

    public function sportyBroadcast(): HasOne
    {
        return $this->hasOne(FixtureBroadcast::class, 'fixture_id')
            ->where('broadcaster_name', 'SportyTV')
            ->latest('id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(BroadcastSession::class, 'fixture_id');
    }

    public function failures(): HasMany
    {
        return $this->hasMany(BroadcastFailure::class, 'fixture_id');
    }

    /**
     * Scope fixtures starting in the future.
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereIn('status', ['scheduled', 'timed'])
            ->where('utc_kickoff', '>=', now())
            ->orderBy('utc_kickoff', 'asc');
    }

    /**
     * Scope fixtures currently live in play.
     */
    public function scopeLive(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereIn('status', ['in_play', 'paused', 'live'])
                ->orWhere(function ($sub) {
                    $sub->where('is_live', true)
                        ->where('utc_kickoff', '<=', now())
                        ->where('utc_kickoff', '>=', now()->subMinutes(125));
                });
        })->orderBy('utc_kickoff', 'asc');
    }

    /**
     * Scope completed matches.
     */
    public function scopeFinished(Builder $query): Builder
    {
        return $query->where('status', 'finished')
            ->orderBy('utc_kickoff', 'desc');
    }

    /**
     * Scope fixtures confirmed on SportyTV.
     */
    public function scopeWithSportyTV(Builder $query): Builder
    {
        return $query->whereHas('broadcasts', function ($q) {
            $q->where('broadcaster_name', 'SportyTV')
                ->whereIn('verification_status', [
                    'confirmed',
                    'authorised_for_distribution',
                    'discovered',
                ]);
        });
    }

    /**
     * Check if the match is genuinely in progress.
     */
    public function getIsLiveNowAttribute(): bool
    {
        if (in_array($this->status, ['in_play', 'paused'])) {
            return true;
        }

        if ($this->utc_kickoff && now()->between($this->utc_kickoff, (clone $this->utc_kickoff)->addMinutes(125))) {
            return ! in_array($this->status, ['finished', 'postponed', 'cancelled']);
        }

        return false;
    }

    /**
     * Seconds remaining until kickoff.
     */
    public function getSecondsUntilKickoffAttribute(): int
    {
        if (! $this->utc_kickoff) {
            return 0;
        }

        return max(0, now()->diffInSeconds($this->utc_kickoff, false));
    }

    /**
     * Formatted West Africa Time (Lagos) Kickoff string.
     */
    public function getLagosFormattedAttribute(): string
    {
        $timezone = config('sports.timezone', 'Africa/Lagos');
        $time = $this->utc_kickoff ? $this->utc_kickoff->setTimezone($timezone) : null;

        return $time ? $time->format('D, M j, Y • g:i A \W\A\T') : 'TBD';
    }

    /**
     * Match display title e.g. "Arsenal vs Manchester City".
     */
    public function getTitleAttribute(): string
    {
        $home = $this->homeTeam?->name ?? 'Home';
        $away = $this->awayTeam?->name ?? 'Away';

        return "{$home} vs {$away}";
    }
}
