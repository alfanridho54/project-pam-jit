<?php

namespace App\Notifications;

use App\Models\JitSession;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JitSessionRevokedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly JitSession $jitSession)
    {
        $this->jitSession->loadMissing(['accessRequest', 'targetServer', 'user']);
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(config('app.name', 'PAM JIT').': JIT session revoked')
            ->greeting('JIT session revoked')
            ->line("Requester: {$this->jitSession->user->name}")
            ->line("Target server: {$this->jitSession->targetServer->name}")
            ->line('Requested duration: '.($this->jitSession->accessRequest?->formattedDuration() ?? 'N/A'))
            ->line('Status: Revoked')
            ->line('Reason: '.($this->jitSession->revoke_reason ?: 'No reason provided.'))
            ->action('View session', route('sessions.show', $this->jitSession))
            ->line('No SSH credentials or Proxmox token secrets are included in this email.');
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
            'duration' => $this->jitSession->accessRequest?->formattedDuration(),
            'revoke_reason' => $this->jitSession->revoke_reason,
            'url' => route('sessions.show', $this->jitSession),
        ];
    }
}
