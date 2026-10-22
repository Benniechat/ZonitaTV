<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'source_type',
        'youtube_channel_id',
        'website_url',
        'category_slug',
        'trust_score',
        'is_verified',
        'is_blocked',
        'notes',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_blocked' => 'boolean',
        'trust_score' => 'integer',
    ];
}
