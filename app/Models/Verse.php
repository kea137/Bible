<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Verse extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'bible_id',
        'book_id',
        'chapter_id',
        'verse_number',
        'text',
    ];

    public function bible()
    {
        return $this->belongsTo(Bible::class);
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function references()
    {
        return $this->hasMany(Reference::class);
    }

    public function reference()
    {
        return $this->belongsTo(Reference::class);
    }

    public function highlight()
{
    return $this->hasOne(VerseHighlight::class, 'verse_id');
}

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'bible_id' => $this->bible_id,
            'book_id' => $this->book_id,
            'chapter_id' => $this->chapter_id,
            'verse_number' => $this->verse_number,
            'text' => $this->text,
        ];
    }
}
