<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Verse extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'chapter_id',
        'verse_number',
        'text',
        'reference_id',
    ];

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
}
