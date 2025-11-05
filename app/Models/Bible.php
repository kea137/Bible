<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Bible extends Model
{
    use Searchable;

    /** @use HasFactory<\Database\Factories\BibleFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'abbreviation',
        'language',
        'version',
        'description',
    ];

    public function books()
    {
        return $this->hasMany(Book::class);
    }

    public function chapters()
    {
        return $this->hasManyThrough(Chapter::class, Book::class);
    }

    public function verses()
    {
        return $this->hasManyThrough(Verse::class, Chapter::class);
    }

    public function references()
    {
        return $this->hasManyThrough(Reference::class, Verse::class);
    }

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'abbreviation' => $this->abbreviation,
            'language' => $this->language,
            'version' => $this->version,
            'description' => $this->description,
        ];
    }
}
