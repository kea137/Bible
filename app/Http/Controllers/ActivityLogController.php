<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    /**
     * Display activity logs (admin only).
     */
    public function index(Request $request)
    {
        Gate::authorize('create', Role::class);

        $query = ActivityLog::with(['user', 'subjectUser'])
            ->orderBy('created_at', 'desc');

        // Filter by action if specified
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by user if specified
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range if specified
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(50)->through(fn ($log) => [
            'id' => $log->id,
            'user' => $log->user ? [
                'id' => $log->user->id,
                'name' => $log->user->name,
                'email' => $log->user->email,
            ] : null,
            'subject_user' => $log->subjectUser ? [
                'id' => $log->subjectUser->id,
                'name' => $log->subjectUser->name,
                'email' => $log->subjectUser->email,
            ] : null,
            'action' => $log->action,
            'description' => $log->description,
            'metadata' => $log->metadata,
            'ip_address' => $log->ip_address,
            'created_at' => $log->created_at->toDateTimeString(),
        ]);

        $actions = ActivityLog::distinct()->pluck('action')->sort()->values();

        return Inertia::render('ActivityLogs', [
            'logs' => $logs,
            'actions' => $actions,
            'filters' => $request->only(['action', 'user_id', 'date_from', 'date_to']),
        ]);
    }
}
