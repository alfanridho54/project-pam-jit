<?php

namespace App\Http\Controllers;

use App\Models\CommandLog;
use App\Models\JitSession;
use App\Services\AuditLogService;
use App\Services\CommandPolicyService;
use App\Services\SshCommandService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SessionCommandController extends Controller
{
    public function index(Request $request, JitSession $jitSession): View
    {
        abort_unless($jitSession->user_id === $request->user()->id, 403);

        $jitSession->load(['targetServer', 'accessRequest']);
        $commandLogs = $jitSession->commandLogs()
            ->latest('executed_at')
            ->latest()
            ->limit(20)
            ->get();

        return view('sessions.commands', compact('jitSession', 'commandLogs'));
    }

    public function store(
        Request $request,
        JitSession $jitSession,
        CommandPolicyService $commandPolicy,
        SshCommandService $sshCommand,
        AuditLogService $auditLog
    ): RedirectResponse {
        $validated = $request->validate([
            'command' => ['required', 'string', 'max:4000'],
        ]);

        $command = trim($validated['command']);
        $jitSession->load('targetServer');

        if ($jitSession->user_id !== $request->user()->id) {
            $commandLog = $this->logAttempt($jitSession, $request->user()->id, $command, CommandLog::STATUS_DENIED, 'User does not own this session.');
            $this->audit($auditLog, $request, $commandLog, 'jit_command_denied');

            abort(403);
        }

        if (! $jitSession->isUsable() || ! $jitSession->targetServer->is_active) {
            $message = ! $jitSession->isUsable()
                ? 'Session is not usable.'
                : 'Target server is inactive.';

            $commandLog = $this->logAttempt($jitSession, $request->user()->id, $command, CommandLog::STATUS_DENIED, $message);
            $this->audit($auditLog, $request, $commandLog, 'jit_command_denied');

            return back()
                ->withInput()
                ->with('error', $message)
                ->with('command_result', ['status' => CommandLog::STATUS_DENIED, 'message' => $message]);
        }

        $policy = $commandPolicy->check($command);

        if (! $policy['allowed']) {
            $commandLog = $this->logAttempt($jitSession, $request->user()->id, $command, CommandLog::STATUS_BLOCKED, $policy['reason']);
            $this->audit($auditLog, $request, $commandLog, 'jit_command_blocked');

            return back()
                ->withInput()
                ->with('error', $policy['reason'])
                ->with('command_result', ['status' => CommandLog::STATUS_BLOCKED, 'message' => $policy['reason']]);
        }

        $result = $sshCommand->executeCommand($jitSession, $command);
        $status = $result['ok'] ? CommandLog::STATUS_SUCCESS : CommandLog::STATUS_FAILED;
        $commandLog = $this->logAttempt(
            $jitSession,
            $request->user()->id,
            $command,
            $status,
            $result['output'] ?? $result['message'],
            $result['exit_code']
        );

        $this->audit($auditLog, $request, $commandLog, $result['ok'] ? 'jit_command_executed' : 'jit_command_failed');

        return back()
            ->with($result['ok'] ? 'success' : 'error', $result['message'])
            ->with('command_result', [
                'status' => $status,
                'message' => $result['message'],
                'output' => $result['output'],
                'exit_code' => $result['exit_code'],
            ]);
    }

    private function logAttempt(
        JitSession $jitSession,
        int $userId,
        string $command,
        string $status,
        ?string $outputExcerpt = null,
        ?int $exitCode = null
    ): CommandLog {
        return CommandLog::create([
            'jit_session_id' => $jitSession->id,
            'user_id' => $userId,
            'target_server_id' => $jitSession->target_server_id,
            'command' => $command,
            'status' => $status,
            'output_excerpt' => $outputExcerpt ? Str::limit($outputExcerpt, 10000, '') : null,
            'exit_code' => $exitCode,
            'executed_at' => now(),
        ]);
    }

    private function audit(AuditLogService $auditLog, Request $request, CommandLog $commandLog, string $action): void
    {
        $auditLog->log(
            $request->user(),
            $action,
            $commandLog,
            "Command {$commandLog->status} for JIT session #{$commandLog->jit_session_id}.",
            [
                'jit_session_id' => $commandLog->jit_session_id,
                'target_server_id' => $commandLog->target_server_id,
                'command' => $commandLog->command,
                'status' => $commandLog->status,
            ]
        );
    }
}
