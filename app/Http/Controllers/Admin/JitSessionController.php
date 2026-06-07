<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessRequest;
use App\Models\JitSession;
use App\Notifications\JitSessionRevokedNotification;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class JitSessionController extends Controller
{
    public function index(): View
    {
        $jitSessions = JitSession::query()
            ->with(['user', 'targetServer', 'accessRequest'])
            ->latest('started_at')
            ->paginate(20);

        return view('admin.sessions.index', compact('jitSessions'));
    }

    public function show(JitSession $jitSession): View
    {
        $jitSession->load(['user', 'targetServer', 'accessRequest', 'revokedBy']);

        return view('admin.sessions.show', compact('jitSession'));
    }

    public function revoke(Request $request, JitSession $jitSession, AuditLogService $auditLog): RedirectResponse
    {
        if (! $jitSession->isActive()) {
            return back()->with('error', 'Only active sessions can be revoked.');
        }

        $validated = $request->validate([
            'revoke_reason' => ['required', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $jitSession, $validated): void {
            $now = now();

            $jitSession->update([
                'status' => JitSession::STATUS_REVOKED,
                'revoked_by' => $request->user()->id,
                'revoked_at' => $now,
                'revoke_reason' => $validated['revoke_reason'],
                'ended_at' => $now,
            ]);

            $jitSession->accessRequest()->update([
                'status' => AccessRequest::STATUS_REVOKED,
            ]);
        });

        $jitSession->load(['user', 'targetServer']);
        $jitSession->user->notify(new JitSessionRevokedNotification($jitSession));

        $auditLog->log(
            $request->user(),
            'jit_session_revoked',
            $jitSession,
            "JIT session #{$jitSession->id} revoked.",
            ['revoke_reason' => $jitSession->revoke_reason, 'access_request_id' => $jitSession->access_request_id]
        );

        return redirect()
            ->route('admin.sessions.show', $jitSession)
            ->with('success', 'JIT session revoked.');
    }
}
