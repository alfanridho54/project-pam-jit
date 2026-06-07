<?php

namespace App\Notifications;

use App\Models\JitSession;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JitSessionRevokedNotification extends Notification
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
            'title' => 'JIT session revoked',
            'message' => "Your JIT session for {$this->jitSession->targetServer->name} was revoked.",
            'target_server_name' => $this->jitSession->targetServer->name,
            'revoke_reason' => $this->jitSession->revoke_reason,
            'url' => route('sessions.show', $this->jitSession),
        ];
    }
}
