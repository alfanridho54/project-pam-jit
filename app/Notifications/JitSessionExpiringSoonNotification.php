<?php

namespace App\Notifications;

use App\Models\JitSession;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JitSessionExpiringSoonNotification extends Notification
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
            ->subject(config('app.name', 'PAM JIT').': JIT session expiring soon')
            ->greeting('JIT session expiring soon')
            ->line("Requester: {$this->jitSession->user->name}")
            ->line("Target server: {$this->jitSession->targetServer->name}")
            ->line('Requested duration: '.($this->jitSession->accessRequest?->formattedDuration() ?? 'N/A'))
            ->line('Status: Expiring soon')
            ->line('Expires at: '.$this->jitSession->expires_at->timezone('Asia/Jakarta')->format('Y-m-d H:i T'))
            ->action('View session', route('sessions.show', $this->jitSession))
            ->line('No SSH credentials or Proxmox token secrets are included in this email.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'JIT session expiring soon',
            'message' => "Your JIT session for {$this->jitSession->targetServer->name} expires soon.",
            'target_server_name' => $this->jitSession->targetServer->name,
            'duration' => $this->jitSession->accessRequest?->formattedDuration(),
            'expires_at' => $this->jitSession->expires_at->toDateTimeString(),
            'url' => route('sessions.show', $this->jitSession),
        ];
    }
}
