<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributionAgreement extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_partner_id',
        'agreement_number',
        'title',
        'agreement_type',
        'exclusivity_type',
        'exclusive_window_days',
        'financial_terms',
        'contract_document_url',
        'start_date',
        'end_date',
        'status',
        'signed_at',
        'content_right_id',
        'terms_summary',
    ];

    protected $casts = [
        'financial_terms' => 'array',
        'exclusive_window_days' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'signed_at' => 'datetime',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(ContentPartner::class, 'content_partner_id');
    }

    public function contentRight(): BelongsTo
    {
        return $this->belongsTo(ContentRight::class);
    }

    public function isExpired(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where(function (Builder $q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>', now());
            });
    }

    public static function generateAgreementNumber(): string
    {
        do {
            $num = 'ZTV-AGR-'.now()->format('Y').'-'.strtoupper(bin2hex(random_bytes(2)));
        } while (static::where('agreement_number', $num)->exists());

        return $num;
    }
}
