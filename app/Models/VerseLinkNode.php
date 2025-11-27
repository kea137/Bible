<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerseLinkNode extends Model
{
    use HasFactory;

    protected $fillable = [
        'canvas_id',
        'verse_id',
        'position_x',
        'position_y',
        'note',
    ];

    public function canvas()
    {
        return $this->belongsTo(VerseLinkCanvas::class, 'canvas_id');
    }

    public function verse()
    {
        return $this->belongsTo(Verse::class);
    }

    public function outgoingConnections()
    {
        return $this->hasMany(VerseLinkConnection::class, 'source_node_id');
    }

    public function incomingConnections()
    {
        return $this->hasMany(VerseLinkConnection::class, 'target_node_id');
    }
}
