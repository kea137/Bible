<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerseHighlight extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'verse_id',
        'color',
        'note',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verse()
    {
        return $this->belongsTo(Verse::class);
    }
}
