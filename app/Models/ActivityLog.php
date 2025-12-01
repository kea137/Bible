<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'subject_user_id',
        'action',
        'description',
        'metadata',
        'ip_address',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the user who performed the action.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who is the subject of the action (if applicable).
     */
    public function subjectUser()
    {
        return $this->belongsTo(User::class, 'subject_user_id');
    }

    /**
     * Log an activity.
     */
    public static function log(string $action, string $description, ?int $subjectUserId = null, ?array $metadata = null): void
    {
        self::create([
            'user_id' => auth()->id(),
            'subject_user_id' => $subjectUserId,
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
        ]);
    }
}
