<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Notifications\DatabaseNotification;

class TeacherNotificationController extends Controller
{
     public function index(Request $request)
    {
        $user = $request->user();
        $query = $user->notifications()->orderByDesc('created_at');
        return ApiResponse::sendResponse(200 , 'success', $query->get());
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        $user->unreadNotifications()->update(['read_at' => now()]);
        return ApiResponse::sendResponse(200 , 'All notifications marked as read');
    }

    public function markAsRead(Request $request, string $id)
    {
        $user = $request->user();
        $notification = $user->notifications()->where('id', $id)->firstOrFail();
        if (is_null($notification->read_at)) {
            $notification->update(['read_at' => now()]);
        }

        return ApiResponse::sendResponse(200 , 'Notification marked as read');
    }

    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $notification = $user->notifications()->where('id', $id)->firstOrFail();
        $notification->delete();

        return ApiResponse::sendResponse(200 , 'Notification deleted');
    }

    public function destroyAll(Request $request)
    {
        $user = $request->user();
        $user->notifications()->delete();
        return ApiResponse::sendResponse(200 , 'All notifications deleted');
    }
}
