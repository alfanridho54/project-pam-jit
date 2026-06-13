<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\JitSession;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Serves the interactive PTY terminal page.
 *
 * Handles authorization, short-lived token generation, and audit logging.
 * The actual WebSocket terminal is handled by the separate Artisan process.
 *
 * @internal Experimental feature — authorised lab use only.
 */
class TerminalController extends Controller
{
    /**
     * Show the interactive terminal page for a JIT session.
     *
     * Authorisation checks (all must pass):
     * 1. User is authenticated (via route middleware)
     * 2. Session belongs to the current user
     * 3. Session is usable (active + not expired)
     * 4. Target server is active
     * 5. SSH credential is available (temporary or stored)
     */
    public function show(Request $request, JitSession $jitSession, AuditLogService $auditLog): View
    {
        $jitSession->load(['targetServer', 'accessRequest']);

        // ── Ownership check ──────────────────────────────────────────────────
        if ($jitSession->user_id !== $request->user()->id) {
            $auditLog->log(
                $request->user(),
                'interactive_terminal_denied',
                $jitSession,
                'Non-owner attempted to open terminal.',
                ['jit_session_id' => $jitSession->id, 'reason' => 'not_owner']
            );
            abort(403, 'You do not own this JIT session.');
        }

        // ── Session usability check ──────────────────────────────────────────
        if (! $jitSession->isUsable()) {
            $auditLog->log(
                $request->user(),
                'interactive_terminal_denied',
                $jitSession,
                'Terminal denied: session is not usable.',
                ['jit_session_id' => $jitSession->id, 'reason' => 'session_not_usable', 'status' => $jitSession->effectiveStatus()]
            );
            abort(403, 'This JIT session is not active or has expired.');
        }

        // ── Target server check ──────────────────────────────────────────────
        if (! $jitSession->targetServer || ! $jitSession->targetServer->is_active) {
            $auditLog->log(
                $request->user(),
                'interactive_terminal_denied',
                $jitSession,
                'Terminal denied: target server is inactive.',
                ['jit_session_id' => $jitSession->id, 'reason' => 'server_inactive']
            );
            abort(403, 'The target server is not active.');
        }

        // ── Credential availability check ────────────────────────────────────
        $hasCredential = $jitSession->hasCreatedTemporaryCredential()
            || ($jitSession->targetServer->auth_type === 'password' && $jitSession->targetServer->hasPassword())
            || ($jitSession->targetServer->auth_type === 'private_key' && $jitSession->targetServer->hasPrivateKey());

        if (! $hasCredential) {
            $auditLog->log(
                $request->user(),
                'interactive_terminal_denied',
                $jitSession,
                'Terminal denied: no SSH credential available.',
                ['jit_session_id' => $jitSession->id, 'reason' => 'no_credential']
            );
            abort(403, 'No SSH credential is available for this session.');
        }

        // ── Generate short-lived encrypted token ─────────────────────────────
        // Token is valid for 5 minutes and contains a nonce for single-use enforcement.
        // The nonce is tracked in-memory on the WebSocket server (not persisted to DB).
        // Limitation: if the server restarts, nonce memory is lost and token could be reused
        // within the 5-minute window. This is acceptable for authorised lab use.
        $tokenPayload = Crypt::encryptString(json_encode([
            'sid' => $jitSession->id,
            'uid' => $request->user()->id,
            'exp' => now()->addMinutes(5)->timestamp,
            'nonce' => Str::uuid()->toString(),
        ]));

        // ── Build WebSocket URL ──────────────────────────────────────────────
        $wsProtocol = $request->secure() ? 'wss' : 'ws';
        $wsUrl = sprintf('%s://%s:8090', $wsProtocol, $request->getHost());

        return view('sessions.terminal', [
            'jitSession' => $jitSession,
            'terminalToken' => $tokenPayload,
            'wsUrl' => $wsUrl,
            'tokenExpiresAt' => now()->addMinutes(5)->toISOString(),
        ]);
    }
}
