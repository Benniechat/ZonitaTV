<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoVerificationLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'video_id',
        'check_type',
        'http_code',
        'status_result',
        'raw_details',
        'created_at',
    ];

    protected $casts = [
        'raw_details' => 'array',
        'created_at' => 'datetime',
        'http_code' => 'integer',
    ];

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }
}
