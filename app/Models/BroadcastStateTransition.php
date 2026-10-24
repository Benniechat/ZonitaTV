<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BroadcastStateTransition extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'from_state',
        'to_state',
        'trigger_job',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(BroadcastSession::class, 'session_id');
    }
}
