<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerseLinkCanvas extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function nodes()
    {
        return $this->hasMany(VerseLinkNode::class, 'canvas_id');
    }

    public function connections()
    {
        return $this->hasMany(VerseLinkConnection::class, 'canvas_id');
    }
}
