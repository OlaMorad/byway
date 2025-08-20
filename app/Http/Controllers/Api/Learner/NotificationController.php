<?php

namespace App\Http\Controllers\Api\Learner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Helpers\ApiResponse;

class NotificationController extends Controller
{

    /**
     * Get all notifications for the authenticated learner
     */
    public function index()
    {
        /** @var \Illuminate\Contracts\Auth\Guard $auth */
        $auth = auth();
        $userId = $auth->id();

        $notifications = Notification::where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notif) {
               // ✅ $notif->data is already an array — no need to json_decode()
            $data = $notif->data; // Already decoded!

                return [
                    'id' => $notif->id,
                    'type' => $notif->type,
                    'title' => $data['title'] ?? 'Notification',
                    'message' => $data['message'] ?? null,
                    'is_read' => (bool) $notif->read_at,
                    'read_at' => $notif->read_at,
                    'created_at' => $notif->created_at->diffForHumans(),
                ];
            });

        return ApiResponse::sendResponse(200, 'Notifications retrieved.', $notifications);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        /** @var \Illuminate\Contracts\Auth\Guard $auth */
        $auth = auth();
        $userId = $auth->id();

        $notification = Notification::where('id', $id)
            ->where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', $userId)
            ->first();

        if (!$notification) {
            return ApiResponse::sendError('Notification not found or unauthorized.', 404);
        }

        if (!$notification->read_at) {
            $notification->markAsRead(); // Laravel's built-in method
        }

        return ApiResponse::sendResponse(200, 'Notification marked as read.');
    }

    /**
     * Delete a notification
     */
    public function destroy($id)
    {
        /** @var \Illuminate\Contracts\Auth\Guard $auth */
        $auth = auth();
        $userId = $auth->id();

        $notification = Notification::where('id', $id)
            ->where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', $userId)
            ->first();

        if (!$notification) {
            return ApiResponse::sendError('Notification not found or unauthorized.', 404);
        }

        $notification->delete();

        return ApiResponse::sendResponse(200, 'Notification deleted successfully.');
    }
}
