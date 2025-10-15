<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    /** @use HasFactory<\Database\Factories\ChapterFactory> */
    use HasFactory;

    protected $fillable = [
        'book_id',
        'title',
        'introduction',
        'chapter_number',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function scriptures()
    {
        return $this->hasMany(Scripture::class);
    }
}
