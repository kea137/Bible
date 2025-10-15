<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reference extends Model
{
    /** @use HasFactory<\Database\Factories\ReferenceFactory> */
    use HasFactory;

    protected $fillable = [
        'bible_id',
        'book_id',
        'chapter_id',
        'verse_id',
        'verse_reference',
        'note',
    ];

    public function bible()
    {
        return $this->belongsTo(Bible::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function verse()
    {
        return $this->belongsTo(Verse::class);
    }
}
