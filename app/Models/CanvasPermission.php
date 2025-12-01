<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CanvasPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'canvas_id',
        'user_id',
        'role',
    ];

    public function canvas()
    {
        return $this->belongsTo(VerseLinkCanvas::class, 'canvas_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the permission is for owner role.
     */
    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    /**
     * Check if the permission is for editor role.
     */
    public function isEditor(): bool
    {
        return $this->role === 'editor';
    }

    /**
     * Check if the permission is for viewer role.
     */
    public function isViewer(): bool
    {
        return $this->role === 'viewer';
    }

    /**
     * Check if the user can edit (owner or editor).
     */
    public function canEdit(): bool
    {
        return in_array($this->role, ['owner', 'editor']);
    }
}
