<?php

namespace App\Notifications;

use App\Models\AccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccessRequestCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly AccessRequest $accessRequest)
    {
        $this->accessRequest->loadMissing(['user', 'targetServer']);
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
            ->subject(config('app.name', 'PAM JIT').': New access request')
            ->greeting('New access request')
            ->line("Requester: {$this->accessRequest->user->name}")
            ->line("Target server: {$this->accessRequest->targetServer->name}")
            ->line('Requested duration: '.$this->accessRequest->formattedDuration())
            ->line('Status: Pending review')
            ->action('Review request', route('admin.access-requests.show', $this->accessRequest))
            ->line('No SSH credentials or Proxmox token secrets are included in this email.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New access request',
            'message' => "{$this->accessRequest->user->name} requested {$this->accessRequest->formattedDuration()} on {$this->accessRequest->targetServer->name}.",
            'requester_name' => $this->accessRequest->user->name,
            'target_server_name' => $this->accessRequest->targetServer->name,
            'duration_minutes' => $this->accessRequest->requested_duration_minutes,
            'duration' => $this->accessRequest->formattedDuration(),
            'reason' => $this->accessRequest->reason,
            'url' => route('admin.access-requests.show', $this->accessRequest),
        ];
    }
}
