<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paragraph extends Model
{
    /** @use HasFactory<\Database\Factories\ParagraphFactory> */
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'title',
        'text',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function verses()
    {
        return $this->hasMany(Verse::class);
    }
}
