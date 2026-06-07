<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'access_request_id',
    'user_id',
    'target_server_id',
    'started_at',
    'expires_at',
    'expiry_warning_sent_at',
    'ended_at',
    'status',
    'revoked_by',
    'revoked_at',
    'revoke_reason',
])]
class JitSession extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_REVOKED = 'revoked';
    public const STATUS_CLOSED = 'closed';

    /**
     * @return array<int, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_EXPIRED,
            self::STATUS_REVOKED,
            self::STATUS_CLOSED,
        ];
    }

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'expiry_warning_sent_at' => 'datetime',
            'ended_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function accessRequest(): BelongsTo
    {
        return $this->belongsTo(AccessRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function targetServer(): BelongsTo
    {
        return $this->belongsTo(TargetServer::class);
    }

    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    public function commandLogs(): HasMany
    {
        return $this->hasMany(CommandLog::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    public function isRevoked(): bool
    {
        return $this->status === self::STATUS_REVOKED;
    }

    public function isUsable(): bool
    {
        return $this->isActive() && $this->expires_at->isFuture();
    }
}
