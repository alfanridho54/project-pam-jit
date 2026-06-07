<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogService
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function log(
        ?User $actor,
        string $action,
        ?Model $target = null,
        ?string $description = null,
        array $metadata = []
    ): AuditLog {
        $request = request();

        return AuditLog::create([
            'actor_id' => $actor?->id,
            'action' => $action,
            'target_type' => $target ? class_basename($target->getMorphClass()) : null,
            'target_id' => $target?->getKey(),
            'description' => $description,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'metadata' => $metadata,
        ]);
    }
}
