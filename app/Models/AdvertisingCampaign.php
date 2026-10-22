<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvertisingCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'advertiser_id',
        'title',
        'placement',
        'banner_image_path',
        'destination_url',
        'start_date',
        'end_date',
        'budget_ngn',
        'status',
        'impressions_count',
        'clicks_count',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget_ngn' => 'decimal:2',
        'impressions_count' => 'integer',
        'clicks_count' => 'integer',
    ];

    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(Advertiser::class);
    }
}
