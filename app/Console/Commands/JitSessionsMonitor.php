<?php

namespace App\Console\Commands;

use App\Models\AccessRequest;
use App\Models\JitSession;
use App\Notifications\JitSessionExpiredNotification;
use App\Notifications\JitSessionExpiringSoonNotification;
use App\Services\AuditLogService;
use App\Services\TemporaryLinuxCredentialService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class JitSessionsMonitor extends Command
{
    protected $signature = 'jit:sessions-monitor';

    protected $description = 'Send JIT session expiry warnings and expire elapsed sessions.';

    public function handle(AuditLogService $auditLog, TemporaryLinuxCredentialService $temporaryCredentials): int
    {
        $now = now();
        $warningCount = $this->sendExpiryWarnings($now, $auditLog);
        $expiredCount = $this->expireSessions($now, $auditLog, $temporaryCredentials);

        $this->info("Sent {$warningCount} expiry warning(s).");
        $this->info("Expired {$expiredCount} JIT session(s).");

        return self::SUCCESS;
    }

    private function sendExpiryWarnings($now, AuditLogService $auditLog): int
    {
        $warningCount = 0;

        JitSession::query()
            ->where('status', JitSession::STATUS_ACTIVE)
            ->whereNull('expiry_warning_sent_at')
            ->where('expires_at', '>', $now)
            ->where('expires_at', '<=', $now->copy()->addMinutes(5))
            ->with(['user', 'targetServer'])
            ->chunkById(100, function ($sessions) use ($now, $auditLog, &$warningCount): void {
                foreach ($sessions as $session) {
                    DB::transaction(function () use ($session, $now, $auditLog, &$warningCount): void {
                        $updated = JitSession::query()
                            ->whereKey($session->id)
                            ->where('status', JitSession::STATUS_ACTIVE)
                            ->whereNull('expiry_warning_sent_at')
                            ->update([
                                'expiry_warning_sent_at' => $now,
                                'updated_at' => $now,
                            ]);

                        if ($updated === 0) {
                            return;
                        }

                        $session->forceFill(['expiry_warning_sent_at' => $now]);
                        $session->user->notify(new JitSessionExpiringSoonNotification($session));

                        $auditLog->log(
                            null,
                            'jit_session_expiry_warning_sent',
                            $session,
                            "Expiry warning sent for JIT session #{$session->id}.",
                            ['expires_at' => $session->expires_at->toDateTimeString(), 'user_id' => $session->user_id]
                        );

                        $warningCount++;
                    });
                }
            });

        return $warningCount;
    }

    private function expireSessions($now, AuditLogService $auditLog, TemporaryLinuxCredentialService $temporaryCredentials): int
    {
        $expiredCount = 0;

        JitSession::query()
            ->where('status', JitSession::STATUS_ACTIVE)
            ->where('expires_at', '<=', $now)
            ->with(['accessRequest', 'user', 'targetServer'])
            ->chunkById(100, function ($sessions) use ($now, $auditLog, $temporaryCredentials, &$expiredCount): void {
                foreach ($sessions as $session) {
                    $expired = DB::transaction(function () use ($session, $now, $auditLog): bool {
                        $updated = JitSession::query()
                            ->whereKey($session->id)
                            ->where('status', JitSession::STATUS_ACTIVE)
                            ->update([
                                'status' => JitSession::STATUS_EXPIRED,
                                'ended_at' => $now,
                                'updated_at' => $now,
                            ]);

                        if ($updated === 0) {
                            return false;
                        }

                        $session->forceFill([
                            'status' => JitSession::STATUS_EXPIRED,
                            'ended_at' => $now,
                        ]);

                        $session->accessRequest()->update([
                            'status' => AccessRequest::STATUS_EXPIRED,
                        ]);

                        $session->user->notify(new JitSessionExpiredNotification($session));

                        $auditLog->log(
                            null,
                            'jit_session_expired',
                            $session,
                            "JIT session #{$session->id} expired.",
                            ['access_request_id' => $session->access_request_id, 'ended_at' => $now->toDateTimeString()]
                        );

                        return true;
                    });

                    if (! $expired) {
                        continue;
                    }

                    $this->cleanupTemporaryCredential($session->refresh(), $temporaryCredentials, $auditLog);
                    $expiredCount++;
                }
            });

        return $expiredCount;
    }

    private function cleanupTemporaryCredential(
        JitSession $session,
        TemporaryLinuxCredentialService $temporaryCredentials,
        AuditLogService $auditLog
    ): void {
        if (! $session->hasCreatedTemporaryCredential()) {
            return;
        }

        $result = $temporaryCredentials->cleanup($session);
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

        $session->update($updates);

        $event = match ($result['status']) {
            JitSession::TEMPORARY_CREDENTIAL_DISABLED => 'temporary_credential_disabled',
            JitSession::TEMPORARY_CREDENTIAL_DELETED => 'temporary_credential_deleted',
            default => 'temporary_credential_cleanup_failed',
        };

        $auditLog->log(
            null,
            $event,
            $session,
            "Temporary credential cleanup processed for expired JIT session #{$session->id}.",
            [
                'temporary_username' => $session->temporary_username,
                'status' => $result['status'],
                'error' => $result['ok'] ? null : $result['message'],
            ]
        );
    }
}
