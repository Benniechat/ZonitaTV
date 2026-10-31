<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SportsTeam extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_name',
        'tla',
        'slug',
        'logo_url',
        'venue_name',
        'country',
        'provider',
        'provider_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (SportsTeam $team) {
            if (empty($team->slug) && ! empty($team->name)) {
                $team->slug = Str::slug($team->name);
            }
        });
    }

    public function homeFixtures(): HasMany
    {
        return $this->hasMany(SportsFixture::class, 'home_team_id');
    }

    public function awayFixtures(): HasMany
    {
        return $this->hasMany(SportsFixture::class, 'away_team_id');
    }

    public function getLogoAttribute(): string
    {
        return $this->logo_url ?: asset('images/logo/zonitatvlogo2.png');
    }
}
