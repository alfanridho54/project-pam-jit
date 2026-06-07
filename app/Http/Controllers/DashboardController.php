<?php

namespace App\Http\Controllers;

use App\Models\AccessRequest;
use App\Models\AuditLog;
use App\Models\CommandLog;
use App\Models\JitSession;
use App\Models\TargetServer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function user(Request $request): View
    {
        $user = $request->user();
        $now = now();
        $todayStart = today();
        $todayEnd = today()->endOfDay();

        $summary = [
            'pending_requests' => AccessRequest::query()
                ->where('user_id', $user->id)
                ->where('status', AccessRequest::STATUS_PENDING)
                ->count(),
            'active_sessions' => JitSession::query()
                ->where('user_id', $user->id)
                ->where('status', JitSession::STATUS_ACTIVE)
                ->where('expires_at', '>', $now)
                ->count(),
            'expired_sessions' => JitSession::query()
                ->where('user_id', $user->id)
                ->where(function ($query) use ($now): void {
                    $query->where('status', JitSession::STATUS_EXPIRED)
                        ->orWhere(function ($query) use ($now): void {
                            $query->where('status', JitSession::STATUS_ACTIVE)
                                ->where('expires_at', '<=', $now);
                        });
                })
                ->count(),
            'command_logs_today' => CommandLog::query()
                ->where('user_id', $user->id)
                ->whereBetween('executed_at', [$todayStart, $todayEnd])
                ->count(),
            'unread_notifications' => $user->unreadNotifications()->count(),
        ];

        $latestAccessRequests = AccessRequest::query()
            ->with(['targetServer', 'jitSession'])
            ->where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        $activeJitSessions = JitSession::query()
            ->with('targetServer')
            ->where('user_id', $user->id)
            ->where('status', JitSession::STATUS_ACTIVE)
            ->where('expires_at', '>', $now)
            ->latest('started_at')
            ->limit(5)
            ->get();

        $latestCommandLogs = CommandLog::query()
            ->with('targetServer')
            ->where('user_id', $user->id)
            ->latest('executed_at')
            ->latest()
            ->limit(5)
            ->get();

        $latestNotifications = $user->notifications()
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'summary',
            'latestAccessRequests',
            'activeJitSessions',
            'latestCommandLogs',
            'latestNotifications',
        ));
    }

    public function admin(): View
    {
        $now = now();
        $todayStart = today();
        $todayEnd = today()->endOfDay();

        $summary = [
            'total_target_servers' => TargetServer::query()->count(),
            'active_target_servers' => TargetServer::query()->where('is_active', true)->count(),
            'pending_access_requests' => AccessRequest::query()
                ->where('status', AccessRequest::STATUS_PENDING)
                ->count(),
            'active_jit_sessions' => JitSession::query()
                ->where('status', JitSession::STATUS_ACTIVE)
                ->where('expires_at', '>', $now)
                ->count(),
            'expired_sessions_today' => JitSession::query()
                ->where('status', JitSession::STATUS_EXPIRED)
                ->whereBetween('ended_at', [$todayStart, $todayEnd])
                ->count(),
            'revoked_sessions_today' => JitSession::query()
                ->where('status', JitSession::STATUS_REVOKED)
                ->whereBetween('revoked_at', [$todayStart, $todayEnd])
                ->count(),
            'command_logs_today' => CommandLog::query()
                ->whereBetween('executed_at', [$todayStart, $todayEnd])
                ->count(),
            'blocked_command_logs_today' => CommandLog::query()
                ->where('status', CommandLog::STATUS_BLOCKED)
                ->whereBetween('executed_at', [$todayStart, $todayEnd])
                ->count(),
        ];

        $latestPendingAccessRequests = AccessRequest::query()
            ->with(['user', 'targetServer', 'jitSession'])
            ->where('status', AccessRequest::STATUS_PENDING)
            ->latest()
            ->limit(5)
            ->get();

        $latestActiveJitSessions = JitSession::query()
            ->with(['user', 'targetServer'])
            ->where('status', JitSession::STATUS_ACTIVE)
            ->where('expires_at', '>', $now)
            ->latest('started_at')
            ->limit(5)
            ->get();

        $latestCommandLogs = CommandLog::query()
            ->with(['user', 'targetServer'])
            ->latest('executed_at')
            ->latest()
            ->limit(5)
            ->get();

        $latestAuditLogs = AuditLog::query()
            ->with('actor')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'summary',
            'latestPendingAccessRequests',
            'latestActiveJitSessions',
            'latestCommandLogs',
            'latestAuditLogs',
        ));
    }
}
