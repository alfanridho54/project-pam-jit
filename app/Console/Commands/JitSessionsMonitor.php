<?php

namespace App\Console\Commands;

use App\Models\AccessRequest;
use App\Models\JitSession;
use App\Notifications\JitSessionExpiredNotification;
use App\Notifications\JitSessionExpiringSoonNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class JitSessionsMonitor extends Command
{
    protected $signature = 'jit:sessions-monitor';

    protected $description = 'Send JIT session expiry warnings and expire elapsed sessions.';

    public function handle(): int
    {
        $now = now();
        $warningCount = $this->sendExpiryWarnings($now);
        $expiredCount = $this->expireSessions($now);

        $this->info("Sent {$warningCount} expiry warning(s).");
        $this->info("Expired {$expiredCount} JIT session(s).");

        return self::SUCCESS;
    }

    private function sendExpiryWarnings($now): int
    {
        $warningCount = 0;

        JitSession::query()
            ->where('status', JitSession::STATUS_ACTIVE)
            ->whereNull('expiry_warning_sent_at')
            ->where('expires_at', '>', $now)
            ->where('expires_at', '<=', $now->copy()->addMinutes(5))
            ->with(['user', 'targetServer'])
            ->chunkById(100, function ($sessions) use ($now, &$warningCount): void {
                foreach ($sessions as $session) {
                    DB::transaction(function () use ($session, $now, &$warningCount): void {
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

                        $warningCount++;
                    });
                }
            });

        return $warningCount;
    }

    private function expireSessions($now): int
    {
        $expiredCount = 0;

        JitSession::query()
            ->where('status', JitSession::STATUS_ACTIVE)
            ->where('expires_at', '<=', $now)
            ->with(['accessRequest', 'user', 'targetServer'])
            ->chunkById(100, function ($sessions) use ($now, &$expiredCount): void {
                foreach ($sessions as $session) {
                    DB::transaction(function () use ($session, $now, &$expiredCount): void {
                        $updated = JitSession::query()
                            ->whereKey($session->id)
                            ->where('status', JitSession::STATUS_ACTIVE)
                            ->update([
                                'status' => JitSession::STATUS_EXPIRED,
                                'ended_at' => $now,
                                'updated_at' => $now,
                            ]);

                        if ($updated === 0) {
                            return;
                        }

                        $session->forceFill([
                            'status' => JitSession::STATUS_EXPIRED,
                            'ended_at' => $now,
                        ]);

                        $session->accessRequest()->update([
                            'status' => AccessRequest::STATUS_EXPIRED,
                        ]);

                        $session->user->notify(new JitSessionExpiredNotification($session));

                        $expiredCount++;
                    });
                }
            });

        return $expiredCount;
    }
}
