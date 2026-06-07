<?php

namespace App\Notifications;

use App\Models\AccessRequest;
use Illuminate\Bus\Queueable;
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
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New access request',
            'message' => "{$this->accessRequest->user->name} requested {$this->accessRequest->requested_duration_minutes} minutes on {$this->accessRequest->targetServer->name}.",
            'requester_name' => $this->accessRequest->user->name,
            'target_server_name' => $this->accessRequest->targetServer->name,
            'duration_minutes' => $this->accessRequest->requested_duration_minutes,
            'reason' => $this->accessRequest->reason,
            'url' => route('admin.access-requests.show', $this->accessRequest),
        ];
    }
}
