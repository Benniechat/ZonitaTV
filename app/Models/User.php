<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'google_id',
        'avatar',
        'email_verified_at',
    ];

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return $this->avatar;
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=ea580c&color=ffffff&bold=true';
    }

    /**
     * Always normalize stored email to lowercase and trimmed.
     */
    public function setEmailAttribute(string $value): void
    {
        $this->attributes['email'] = strtolower(trim($value));
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function preferences(): HasOne
    {
        return $this->hasOne(UserPreference::class);
    }

    /**
     * Retrieve or idempotently initialize user preferences.
     */
    public function getOrCreatePreferences(): UserPreference
    {
        if ($this->relationLoaded('preferences') && $this->preferences) {
            return $this->preferences;
        }

        $existing = $this->preferences()->first();
        if ($existing) {
            return $existing;
        }

        return $this->preferences()->create([
            'country_code' => 'NGA',
            'enable_recommendations' => true,
        ]);
    }

    public function favourites(): HasMany
    {
        return $this->hasMany(Favourite::class);
    }

    public function viewingEvents(): HasMany
    {
        return $this->hasMany(ViewingEvent::class);
    }

    public function favouriteChannels(): BelongsToMany
    {
        return $this->belongsToMany(Channel::class, 'favourites');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isContentManager(): bool
    {
        return in_array($this->role, ['content_manager', 'super_admin']);
    }

    public function isRightsReviewer(): bool
    {
        return in_array($this->role, ['rights_reviewer', 'super_admin']);
    }

    public function isAdvertisingManager(): bool
    {
        return in_array($this->role, ['advertising_manager', 'super_admin']);
    }

    public function canAccessAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'content_manager', 'rights_reviewer', 'advertising_manager']);
    }
}
