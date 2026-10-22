<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsletterSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'topics',
        'is_active',
        'unsubscribe_token',
        'unsubscribed_at',
    ];

    protected $casts = [
        'topics' => 'array',
        'is_active' => 'boolean',
        'unsubscribed_at' => 'datetime',
    ];
}
