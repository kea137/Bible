<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Topic extends Model
{
    protected $fillable = [
        'title',
        'description',
        'category',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the verses associated with this topic (verse chain)
     */
    public function verses(): BelongsToMany
    {
        return $this->belongsToMany(Verse::class, 'topic_verses')
            ->withPivot('order', 'note')
            ->withTimestamps()
            ->orderBy('topic_verses.order');
    }
}
