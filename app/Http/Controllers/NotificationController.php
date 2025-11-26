<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get unread notifications count and latest notifications HTML
     * 
     * This endpoint is polled by frontend JavaScript every 5 seconds
     * Returns JSON with count and HTML markup for notification dropdown
     */
    public function unread(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'count' => 0,
                'html' => '<div class="px-4 py-8 text-center text-gray-500 text-sm">Please log in to view notifications</div>',
            ]);
        }

        // Get unread notifications count
        $unreadCount = $user->unreadNotifications()->count();

        // Get latest 5 notifications (mix of read and unread for context)
        $notifications = $user->notifications()->take(5)->get();

        // Generate HTML for notifications
        $html = view('components.notification-items', compact('notifications'))->render();

        return response()->json([
            'count' => $unreadCount,
            'html' => $html,
        ]);
    }

    /**
     * Mark a specific notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        $user = Auth::user();
        
        $notification = $user->notifications()->find($id);
        
        if ($notification) {
            $notification->markAsRead();
            
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Notification not found',
        ], 404);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();
        
        $user->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Display the full Notification Center page
     * 
     * Shows paginated list of all notifications (read and unread)
     * with visual distinction and filtering capabilities
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get notifications with pagination (10 per page)
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        // Get unread count for header display
        $unreadCount = $user->unreadNotifications()->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Delete a notification
     */
    public function destroy(Request $request, $id)
    {
        $user = Auth::user();
        
        $notification = $user->notifications()->find($id);
        
        if ($notification) {
            $notification->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Notification deleted',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Notification not found',
        ], 404);
    }
}
