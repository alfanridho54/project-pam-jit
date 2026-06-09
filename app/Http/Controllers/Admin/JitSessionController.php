<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessRequest;
use App\Models\JitSession;
use App\Notifications\JitSessionRevokedNotification;
use App\Services\AuditLogService;
use App\Services\TemporaryLinuxCredentialService;
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

    public function revoke(
        Request $request,
        JitSession $jitSession,
        AuditLogService $auditLog,
        TemporaryLinuxCredentialService $temporaryCredentials
    ): RedirectResponse
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

        $cleanup = $this->cleanupTemporaryCredential($jitSession, $temporaryCredentials, $auditLog, $request);

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
            ->with($cleanup['ok'] ? 'success' : 'error', $cleanup['message'] ?? 'JIT session revoked.');
    }

    /**
     * @return array{ok: bool, message: string}
     */
    private function cleanupTemporaryCredential(
        JitSession $jitSession,
        TemporaryLinuxCredentialService $temporaryCredentials,
        AuditLogService $auditLog,
        Request $request
    ): array {
        $jitSession->refresh();

        if (! $jitSession->hasCreatedTemporaryCredential()) {
            return ['ok' => true, 'message' => 'JIT session revoked.'];
        }

        $result = $temporaryCredentials->cleanup($jitSession);
        $now = now();
        $updates = [
            'temporary_credential_status' => $result['status'],
            'temporary_credential_error' => $result['ok'] ? null : $result['message'],
        ];

        if ($result['status'] === JitSession::TEMPORARY_CREDENTIAL_DISABLED) {
            $updates['temporary_credential_disabled_at'] = $now;
        }

        if ($result['status'] === JitSession::TEMPORARY_CREDENTIAL_DELETED) {
            $updates['temporary_credential_deleted_at'] = $now;
        }

        $jitSession->update($updates);

        $event = match ($result['status']) {
            JitSession::TEMPORARY_CREDENTIAL_DISABLED => 'temporary_credential_disabled',
            JitSession::TEMPORARY_CREDENTIAL_DELETED => 'temporary_credential_deleted',
            default => 'temporary_credential_cleanup_failed',
        };

        $auditLog->log(
            $request->user(),
            $event,
            $jitSession,
            "Temporary credential cleanup processed for JIT session #{$jitSession->id}.",
            [
                'temporary_username' => $jitSession->temporary_username,
                'status' => $result['status'],
                'error' => $result['ok'] ? null : $result['message'],
            ]
        );

        return [
            'ok' => $result['ok'],
            'message' => $result['ok']
                ? 'JIT session revoked and temporary credential cleaned up.'
                : 'JIT session revoked, but temporary credential cleanup failed: '.$result['message'],
        ];
    }
}
