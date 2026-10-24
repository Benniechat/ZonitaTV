<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentComplaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'complainant_name',
        'complainant_email',
        'complainant_organization',
        'relationship_to_owner',
        'complaint_type',
        'copyrighted_work_description',
        'infringing_url',
        'content_reference',
        'video_id',
        'channel_id',
        'infringement_details',
        'good_faith_declaration',
        'authority_confirmation',
        'status',
        'ip_address',
        'action_taken',
        'resolution_notes',
        'resolved_by_user_id',
        'resolved_at',
    ];

    protected $casts = [
        'good_faith_declaration' => 'boolean',
        'authority_confirmation' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }

    /**
     * Generate a unique, professional complaint reference number.
     * Format: ZTV-CMP-YYYYMMDD-XXXX
     */
    public static function generateReferenceNumber(): string
    {
        do {
            $ref = 'ZTV-CMP-'.now()->format('Ymd').'-'.strtoupper(bin2hex(random_bytes(2)));
        } while (static::where('reference_number', $ref)->exists());

        return $ref;
    }
}
