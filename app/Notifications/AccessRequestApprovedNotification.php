<?php

namespace App\Notifications;

use App\Models\AccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccessRequestApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly AccessRequest $accessRequest)
    {
        $this->accessRequest->loadMissing(['user', 'targetServer', 'jitSession']);
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
        $jitSession = $this->accessRequest->jitSession;

        $message = (new MailMessage)
            ->subject(config('app.name', 'PAM JIT').': Access request approved')
            ->greeting('Access request approved')
            ->line("Requester: {$this->accessRequest->user->name}")
            ->line("Target server: {$this->accessRequest->targetServer->name}")
            ->line('Requested duration: '.$this->accessRequest->formattedDuration())
            ->line('Status: Approved');

        if ($jitSession?->expires_at) {
            $message->line('Session expires at: '.$jitSession->expires_at->timezone('Asia/Jakarta')->format('Y-m-d H:i T'));
        }

        return $message
            ->action('Open session', $jitSession ? route('sessions.show', $jitSession) : route('requests.show', $this->accessRequest))
            ->line('No SSH credentials or Proxmox token secrets are included in this email.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $jitSession = $this->accessRequest->jitSession;

        return [
            'title' => 'Access request approved',
            'message' => "Your request for {$this->accessRequest->targetServer->name} was approved.",
            'target_server_name' => $this->accessRequest->targetServer->name,
            'duration' => $this->accessRequest->formattedDuration(),
            'expires_at' => $jitSession?->expires_at?->toDateTimeString(),
            'url' => $jitSession ? route('sessions.show', $jitSession) : route('requests.show', $this->accessRequest),
        ];
    }
}
