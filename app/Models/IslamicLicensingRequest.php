<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IslamicLicensingRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'contact_name',
        'contact_email',
        'subject',
        'proposal_model',
        'proposed_territories',
        'licence_term_months',
        'status',
        'sent_at',
        'notes',
    ];

    protected $casts = [
        'proposed_territories' => 'array',
        'licence_term_months' => 'integer',
        'sent_at' => 'datetime',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(IslamicContentProvider::class, 'provider_id');
    }
}
