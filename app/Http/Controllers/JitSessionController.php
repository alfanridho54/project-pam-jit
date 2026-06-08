<?php

namespace App\Http\Controllers;

use App\Models\JitSession;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class JitSessionController extends Controller
{
    public function index(Request $request): View
    {
        $jitSessions = JitSession::query()
            ->with(['accessRequest', 'targetServer'])
            ->where('user_id', $request->user()->id)
            ->latest('started_at')
            ->paginate(15);

        return view('sessions.index', compact('jitSessions'));
    }

    public function show(Request $request, JitSession $jitSession, AuditLogService $auditLog): View
    {
        abort_unless($jitSession->user_id === $request->user()->id, 403);

        $jitSession->load(['accessRequest', 'targetServer', 'revokedBy']);

        if ($jitSession->isUsable()) {
            $auditLog->log(
                $request->user(),
                'jit_sftp_profile_viewed',
                $jitSession,
                "SFTP profile viewed for JIT session #{$jitSession->id}.",
                [
                    'target_server_id' => $jitSession->target_server_id,
                    'expires_at' => $jitSession->expires_at?->toDateTimeString(),
                ]
            );
        }

        return view('sessions.show', compact('jitSession'));
    }

    public function downloadSftpProfile(Request $request, JitSession $jitSession, AuditLogService $auditLog): Response
    {
        abort_unless($jitSession->user_id === $request->user()->id, 403);

        $jitSession->load('targetServer');

        abort_unless($jitSession->isUsable(), 404);

        $targetServer = $jitSession->targetServer;
        $sessionUrl = sprintf(
            'sftp://%s@%s:%d/',
            rawurlencode($targetServer->ssh_username),
            $targetServer->host,
            $targetServer->port
        );

        $contents = implode(PHP_EOL, [
            'WinSCP / SFTP Profile',
            '',
            'Protocol: SFTP',
            "Host: {$targetServer->host}",
            "Port: {$targetServer->port}",
            "Username: {$targetServer->ssh_username}",
            "Target server: {$targetServer->name}",
            'Session URL: '.$sessionUrl,
            'Expires at: '.$jitSession->expires_at->timezone('Asia/Jakarta')->format('Y-m-d H:i T'),
            '',
            'Note: Credentials are managed by PAM and are intentionally not included in this file.',
            'File transfer access is only allowed during the active JIT session.',
            'After expiry or revocation, this access should no longer be considered valid.',
            '',
        ]);

        $auditLog->log(
            $request->user(),
            'jit_sftp_profile_downloaded',
            $jitSession,
            "SFTP profile downloaded for JIT session #{$jitSession->id}.",
            [
                'target_server_id' => $jitSession->target_server_id,
                'expires_at' => $jitSession->expires_at?->toDateTimeString(),
            ]
        );

        return response($contents, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="winscp-jit-session-'.$jitSession->id.'.txt"',
        ]);
    }
}
