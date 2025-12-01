<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerseLinkConnection extends Model
{
    use HasFactory;

    protected $fillable = [
        'canvas_id',
        'source_node_id',
        'target_node_id',
        'label',
        'version',
        'last_modified_by',
        'last_modified_at',
    ];

    protected $casts = [
        'last_modified_at' => 'datetime',
    ];

    public function canvas()
    {
        return $this->belongsTo(VerseLinkCanvas::class, 'canvas_id');
    }

    public function lastModifiedBy()
    {
        return $this->belongsTo(User::class, 'last_modified_by');
    }

    public function sourceNode()
    {
        return $this->belongsTo(VerseLinkNode::class, 'source_node_id');
    }

    public function targetNode()
    {
        return $this->belongsTo(VerseLinkNode::class, 'target_node_id');
    }
}
