<?php

namespace App\Notifications;

use App\Models\AccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AccessRequestApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly AccessRequest $accessRequest)
    {
        $this->accessRequest->loadMissing(['targetServer', 'jitSession']);
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
        $jitSession = $this->accessRequest->jitSession;

        return [
            'title' => 'Access request approved',
            'message' => "Your request for {$this->accessRequest->targetServer->name} was approved.",
            'target_server_name' => $this->accessRequest->targetServer->name,
            'expires_at' => $jitSession?->expires_at?->toDateTimeString(),
            'url' => $jitSession ? route('sessions.show', $jitSession) : route('requests.show', $this->accessRequest),
        ];
    }
}
