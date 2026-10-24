<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BroadcastSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'fixture_id',
        'channel_id',
        'title',
        'operating_mode',
        'backend',
        'current_state',
        'input_source',
        'output_target',
        'scheduled_start_at',
        'actual_start_at',
        'ended_at',
        'notes',
    ];

    protected $casts = [
        'scheduled_start_at' => 'datetime',
        'actual_start_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (BroadcastSession $session) {
            if (empty($session->title)) {
                $fixture = $session->fixture ?? SportsFixture::find($session->fixture_id);
                $session->title = $fixture
                    ? (($fixture->homeTeam?->name ?? 'Home').' vs '.($fixture->awayTeam?->name ?? 'Away'))
                    : 'Live Sports Broadcast Session';
            }
        });
    }

    public function fixture(): BelongsTo
    {
        return $this->belongsTo(SportsFixture::class, 'fixture_id');
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class, 'channel_id');
    }

    public function transitions(): HasMany
    {
        return $this->hasMany(BroadcastStateTransition::class, 'session_id')->orderBy('id', 'asc');
    }

    public function failures(): HasMany
    {
        return $this->hasMany(BroadcastFailure::class, 'session_id');
    }

    /**
     * Record a failure incident during this broadcast session.
     */
    public function recordFailure(string $failureType, string $errorMessage, array $details = []): BroadcastFailure
    {
        return $this->failures()->create([
            'failure_type' => $failureType,
            'error_message' => $errorMessage,
            'details' => $details,
            'resolved' => false,
        ]);
    }

    /**
     * Transition the broadcast state and record audit log.
     */
    public function transitionTo(string $newState, ?string $triggerJob = null, array $details = []): bool
    {
        $oldState = $this->current_state;

        $this->update(['current_state' => $newState]);

        $this->transitions()->create([
            'from_state' => $oldState,
            'to_state' => $newState,
            'trigger_job' => $triggerJob,
            'details' => $details,
        ]);

        return true;
    }
}
