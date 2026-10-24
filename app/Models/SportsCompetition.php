<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SportsCompetition extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'country',
        'emblem_url',
        'provider',
        'provider_id',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    public function fixtures(): HasMany
    {
        return $this->hasMany(SportsFixture::class, 'competition_id');
    }

    public function getEmblemAttribute(): string
    {
        return $this->emblem_url ?: asset('images/logo/zonitatvlogo2.png');
    }
}
