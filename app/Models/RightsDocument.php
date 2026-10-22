<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RightsDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_right_id',
        'title',
        'document_path',
        'file_type',
        'notes',
    ];

    public function contentRight(): BelongsTo
    {
        return $this->belongsTo(ContentRight::class);
    }
}
