<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VerseLinkCanvas extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'share_token',
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

    /**
     * Generate a unique share token for this canvas.
     */
    public function generateShareToken(): string
    {
        $token = Str::random(32);
        
        // Ensure uniqueness
        while (self::where('share_token', $token)->exists()) {
            $token = Str::random(32);
        }
        
        $this->share_token = $token;
        $this->save();
        
        return $token;
    }

    /**
     * Revoke the share token.
     */
    public function revokeShareToken(): void
    {
        $this->share_token = null;
        $this->save();
    }
}
