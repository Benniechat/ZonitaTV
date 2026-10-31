<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'provider_type',
        'website_url',
        'source_feed_url',
        'contact_email',
        'contact_name',
        'default_rights_basis',
        'is_trusted',
        'is_active',
        'notes',
        'last_scanned_at',
    ];

    protected $casts = [
        'is_trusted' => 'boolean',
        'is_active' => 'boolean',
        'last_scanned_at' => 'datetime',
    ];

    public function candidates(): HasMany
    {
        return $this->hasMany(DiscoveryCandidate::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeTrusted(Builder $query): Builder
    {
        return $query->where('is_trusted', true);
    }
}
