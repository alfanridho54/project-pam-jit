<?php

namespace App\Notifications;

use App\Models\JitSession;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JitSessionExpiredNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly JitSession $jitSession)
    {
        $this->jitSession->loadMissing('targetServer');
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'JIT session expired',
            'message' => "Your JIT session for {$this->jitSession->targetServer->name} has expired.",
            'target_server_name' => $this->jitSession->targetServer->name,
            'expires_at' => $this->jitSession->expires_at->toDateTimeString(),
            'ended_at' => $this->jitSession->ended_at?->toDateTimeString(),
            'url' => route('sessions.show', $this->jitSession),
        ];
    }
}
