<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    /** @use HasFactory<\Database\Factories\ChapterFactory> */
    use HasFactory;

    protected $fillable = [
        'bible_id',
        'book_id',
        'chapter_number',
        'title',
        'introduction',
    ];

    public function bible()
    {
        return $this->belongsTo(Bible::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function verses()
    {
    return $this->hasMany(Verse::class);
    }

    public function references()
    {
        return $this->hasMany(Reference::class);
    }

    public function reference()
    {
        return $this->belongsTo(Reference::class);
    }

    public function readingProgress()
    {
        return $this->hasMany(ReadingProgress::class);
    }
}
