<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SportsBroadcastRight extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'rights_holder',
        'territory',
        'valid_from',
        'valid_until',
        'allow_mode_a',
        'allow_mode_b',
        'allow_mode_c',
        'document_reference',
        'status',
        'notes',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'allow_mode_a' => 'boolean',
        'allow_mode_b' => 'boolean',
        'allow_mode_c' => 'boolean',
    ];

    public function isValidNow(): bool
    {
        return $this->status === 'approved' && now()->between($this->valid_from, $this->valid_until);
    }
}
