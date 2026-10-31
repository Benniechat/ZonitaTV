<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ContentPartner extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (ContentPartner $partner): void {
            if (empty($partner->slug)) {
                $baseSlug = Str::slug($partner->name) ?: 'partner';
                $slug = $baseSlug;
                $counter = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $baseSlug.'-'.$counter;
                    $counter++;
                }
                $partner->slug = $slug;
            }
        });
    }

    protected $fillable = [
        'name',
        'slug',
        'partner_type',
        'pipeline_stage',
        'contact_name',
        'contact_email',
        'contact_phone',
        'website_url',
        'country_code',
        'assigned_agent_id',
        'lead_source',
        'notes',
    ];

    public function agreements(): HasMany
    {
        return $this->hasMany(DistributionAgreement::class);
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    public function channels(): HasMany
    {
        return $this->hasMany(Channel::class);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }

    public function advanceStage(string $newStage): bool
    {
        $validStages = [
            'prospect',
            'contact_made',
            'negotiation',
            'agreement_signed',
            'content_received',
            'active',
            'churned',
        ];

        if (! in_array($newStage, $validStages)) {
            return false;
        }

        $this->pipeline_stage = $newStage;

        return $this->save();
    }

    public function isActive(): bool
    {
        return $this->pipeline_stage === 'active';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('pipeline_stage', 'active');
    }

    public function scopeByStage(Builder $query, string $stage): Builder
    {
        return $query->where('pipeline_stage', $stage);
    }

    public function scopeProspects(Builder $query): Builder
    {
        return $query->whereIn('pipeline_stage', ['prospect', 'contact_made', 'negotiation']);
    }
}
