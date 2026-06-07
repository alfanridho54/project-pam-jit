<?php

namespace App\Notifications;

use App\Models\AccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AccessRequestRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly AccessRequest $accessRequest)
    {
        $this->accessRequest->loadMissing('targetServer');
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
            'title' => 'Access request rejected',
            'message' => "Your request for {$this->accessRequest->targetServer->name} was rejected.",
            'target_server_name' => $this->accessRequest->targetServer->name,
            'rejection_reason' => $this->accessRequest->rejection_reason,
            'url' => route('requests.show', $this->accessRequest),
        ];
    }
}
