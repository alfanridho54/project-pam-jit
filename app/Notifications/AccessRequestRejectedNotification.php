<?php

namespace App\Notifications;

use App\Models\AccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccessRequestRejectedNotification extends Notification
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
            ->subject(config('app.name', 'PAM JIT').': Access request rejected')
            ->greeting('Access request rejected')
            ->line("Requester: {$this->accessRequest->user->name}")
            ->line("Target server: {$this->accessRequest->targetServer->name}")
            ->line('Requested duration: '.$this->accessRequest->formattedDuration())
            ->line('Status: Rejected')
            ->line('Reason: '.($this->accessRequest->rejection_reason ?: 'No reason provided.'))
            ->action('View request', route('requests.show', $this->accessRequest))
            ->line('No SSH credentials or Proxmox token secrets are included in this email.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Access request rejected',
            'message' => "Your request for {$this->accessRequest->targetServer->name} was rejected.",
            'target_server_name' => $this->accessRequest->targetServer->name,
            'duration' => $this->accessRequest->formattedDuration(),
            'rejection_reason' => $this->accessRequest->rejection_reason,
            'url' => route('requests.show', $this->accessRequest),
        ];
    }
}
