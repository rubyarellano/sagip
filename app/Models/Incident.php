<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incident extends Model
{
    protected $fillable = [
        'user_id',
        'category',
        'severity',
        'latitude',
        'longitude',
        'address',
        'status',
        'description',
        'photo_path',
        'reporter_name',
        'reporter_phone',
    ];

    /**
     * Get the user that reported the incident.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the custom formatted incident code (e.g. AH-031).
     */
    public function getCodeAttribute(): string
    {
        return 'AH-' . str_pad($this->id, 3, '0', STR_PAD_LEFT);
    }
}
