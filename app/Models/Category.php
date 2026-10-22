<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon_name',
        'meta_title',
        'description',
        'sort_order',
        'is_nav_visible',
    ];

    protected $casts = [
        'is_nav_visible' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(Channel::class, 'channel_categories');
    }

    public function programmes(): HasMany
    {
        return $this->hasMany(Programme::class);
    }
}
