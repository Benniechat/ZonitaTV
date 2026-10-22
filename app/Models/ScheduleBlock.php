<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel_id',
        'name',
        'start_hour',
        'end_hour',
        'preferred_format',
        'days_of_week',
        'is_active',
    ];

    protected $casts = [
        'start_hour' => 'integer',
        'end_hour' => 'integer',
        'days_of_week' => 'array',
        'is_active' => 'boolean',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }
}
