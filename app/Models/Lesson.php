<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Lesson extends Model
{
    protected $fillable = [
        'title',
        'description',
        'readable',
        'language',
        'user_id',
        'no_paragraphs',
        'series_id',
        'episode_number',
    ];

    /** @use HasFactory<\Database\Factories\LessonFactory> */
    use HasFactory;

    use Searchable;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paragraphs()
    {
        return $this->hasMany(Paragraph::class);
    }

    public function series()
    {
        return $this->belongsTo(LessonSeries::class, 'series_id');
    }

    public function progress()
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function userProgress($userId)
    {
        return $this->hasOne(LessonProgress::class)->where('user_id', $userId);
    }

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'language' => $this->language,
            'user_id' => $this->user_id,
            'series_id' => $this->series_id,
            'episode_number' => $this->episode_number,
            'created_at' => $this->created_at?->timestamp,
        ];
    }
}
