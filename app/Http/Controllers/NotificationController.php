<?php

namespace App\Http\Controllers;

use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        $unreadCount = $request->user()->unreadNotifications()->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function markAsRead(Request $request, string $notification, AuditLogService $auditLog): RedirectResponse
    {
        $notification = $request->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        $notification->markAsRead();

        $auditLog->log(
            $request->user(),
            'notification_marked_read',
            null,
            "Notification {$notification->id} marked as read.",
            ['notification_id' => $notification->id, 'notification_type' => $notification->type]
        );

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead(Request $request, AuditLogService $auditLog): RedirectResponse
    {
        $count = $request->user()->unreadNotifications()->count();

        $request->user()->unreadNotifications->markAsRead();

        $auditLog->log(
            $request->user(),
            'notifications_marked_all_read',
            null,
            'All unread notifications marked as read.',
            ['count' => $count]
        );

        return back()->with('success', 'All notifications marked as read.');
    }
}
