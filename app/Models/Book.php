<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    /** @use HasFactory<\Database\Factories\BookFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'published_year',
        'introduction',
        'summary',
        'book_number',
    ];

    public function chapters()
    {
        return $this->hasMany(Chapter::class);
    }
}
