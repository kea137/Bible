<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemoryVerse extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'verse_id',
        'repetitions',
        'easiness_factor',
        'interval',
        'next_review_date',
        'last_reviewed_at',
        'total_reviews',
        'correct_reviews',
    ];

    protected $casts = [
        'easiness_factor' => 'float',
        'next_review_date' => 'date',
        'last_reviewed_at' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verse(): BelongsTo
    {
        return $this->belongsTo(Verse::class);
    }

    /**
     * Check if this memory verse is due for review
     */
    public function isDue(): bool
    {
        return $this->next_review_date <= now()->toDateString();
    }

    /**
     * Get the success rate for this memory verse
     */
    public function getSuccessRateAttribute(): float
    {
        if ($this->total_reviews === 0) {
            return 0;
        }

        return round(($this->correct_reviews / $this->total_reviews) * 100, 2);
    }
}
