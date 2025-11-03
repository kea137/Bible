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
        'no_paragraphs'
    ];

    /** @use HasFactory<\Database\Factories\LessonFactory> */
    use HasFactory;
    use Searchable;

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function paragraphs(){
        return $this->hasMany(Paragraph::class);
    }
}
