<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Only allow role numbers 1 and 2 to see notifications
        $roleNumbers = $user->roles()->pluck('role_number')->toArray();
        if (!in_array(1, $roleNumbers) && !in_array(2, $roleNumbers)) {
            return response()->json(['notifications' => [], 'unread_count' => 0]);
        }
        
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        $unreadCount = $notifications->where('read', false)->count();
        
        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }
    
    /**
     * Get unread count
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user();
        
        // Only allow role numbers 1 and 2 to see notifications
        $roleNumbers = $user->roles()->pluck('role_number')->toArray();
        if (!in_array(1, $roleNumbers) && !in_array(2, $roleNumbers)) {
            return response()->json(['count' => 0]);
        }
        
        $count = Notification::where('user_id', $user->id)
            ->where('read', false)
            ->count();
            
        return response()->json(['count' => $count]);
    }
    
    /**
     * Mark a notification as read
     */
    public function markAsRead(Request $request, Notification $notification)
    {
        // Ensure the notification belongs to the authenticated user
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $notification->update(['read' => true]);
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        
        Notification::where('user_id', $user->id)
            ->where('read', false)
            ->update(['read' => true]);
            
        return response()->json(['success' => true]);
    }
    
    /**
     * Delete a notification
     */
    public function destroy(Request $request, Notification $notification)
    {
        // Ensure the notification belongs to the authenticated user
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $notification->delete();
        
        return response()->json(['success' => true]);
    }
}
