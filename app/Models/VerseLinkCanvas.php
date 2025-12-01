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
        'is_collaborative',
    ];

    protected $casts = [
        'is_collaborative' => 'boolean',
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

    public function permissions()
    {
        return $this->hasMany(CanvasPermission::class, 'canvas_id');
    }

    public function collaborators()
    {
        return $this->belongsToMany(User::class, 'canvas_permissions', 'canvas_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the permission for a specific user.
     */
    public function getPermissionForUser(int $userId): ?CanvasPermission
    {
        return $this->permissions()->where('user_id', $userId)->first();
    }

    /**
     * Check if a user has access to this canvas.
     */
    public function userHasAccess(int $userId): bool
    {
        // Owner always has access
        if ($this->user_id === $userId) {
            return true;
        }

        // Check if user has any permission
        return $this->permissions()->where('user_id', $userId)->exists();
    }

    /**
     * Check if a user can edit this canvas.
     */
    public function userCanEdit(int $userId): bool
    {
        // Owner can always edit
        if ($this->user_id === $userId) {
            return true;
        }

        // Check if user has owner or editor permission
        $permission = $this->getPermissionForUser($userId);
        return $permission && $permission->canEdit();
    }
}
