<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BroadcastFailure extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'fixture_id',
        'failure_type',
        'severity',
        'retry_count',
        'resolved',
        'fallback_activated',
        'error_message',
        'diagnostic_data',
    ];

    protected $casts = [
        'resolved' => 'boolean',
        'fallback_activated' => 'boolean',
        'retry_count' => 'integer',
        'diagnostic_data' => 'array',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(BroadcastSession::class, 'session_id');
    }

    public function fixture(): BelongsTo
    {
        return $this->belongsTo(SportsFixture::class, 'fixture_id');
    }
}
