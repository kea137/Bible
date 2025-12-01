<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerseSuggestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'verse_id',
        'score',
        'reasons',
        'dismissed',
        'clicked',
    ];

    protected $casts = [
        'score' => 'float',
        'reasons' => 'array',
        'dismissed' => 'boolean',
        'clicked' => 'boolean',
    ];

    /**
     * Get the user who received this suggestion
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the suggested verse
     */
    public function verse(): BelongsTo
    {
        return $this->belongsTo(Verse::class);
    }
}
