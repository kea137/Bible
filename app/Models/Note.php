<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class Note extends Model
{
    /** @use HasFactory<\Database\Factories\NoteFactory> */
    use HasFactory;

    use Searchable;

    protected $fillable = [
        'user_id',
        'verse_id',
        'title',
        'content',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verse(): BelongsTo
    {
        return $this->belongsTo(Verse::class);
    }

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'verse_id' => $this->verse_id,
            'title' => $this->title,
            'content' => $this->content,
            'created_at' => $this->created_at?->timestamp,
            'updated_at' => $this->updated_at?->timestamp,
        ];
    }
}
