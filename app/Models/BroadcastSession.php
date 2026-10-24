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
        return $this->hasMany(BroadcastStateTransition::class, 'session_id')->latest('created_at');
    }

    public function failures(): HasMany
    {
        return $this->hasMany(BroadcastFailure::class, 'session_id');
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
