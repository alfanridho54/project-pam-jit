<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
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
    'uses_temporary_credential',
    'temporary_username',
    'temporary_password_encrypted',
    'temporary_credential_status',
    'temporary_credential_created_at',
    'temporary_credential_disabled_at',
    'temporary_credential_deleted_at',
    'temporary_credential_error',
])]
#[Hidden(['temporary_password_encrypted'])]
class JitSession extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_REVOKED = 'revoked';
    public const STATUS_CLOSED = 'closed';

    public const TEMPORARY_CREDENTIAL_PENDING = 'pending';
    public const TEMPORARY_CREDENTIAL_CREATED = 'created';
    public const TEMPORARY_CREDENTIAL_CREATE_FAILED = 'create_failed';
    public const TEMPORARY_CREDENTIAL_DISABLED = 'disabled';
    public const TEMPORARY_CREDENTIAL_DISABLE_FAILED = 'disable_failed';
    public const TEMPORARY_CREDENTIAL_DELETED = 'deleted';
    public const TEMPORARY_CREDENTIAL_DELETE_FAILED = 'delete_failed';

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
            'uses_temporary_credential' => 'boolean',
            'temporary_credential_created_at' => 'datetime',
            'temporary_credential_disabled_at' => 'datetime',
            'temporary_credential_deleted_at' => 'datetime',
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

    public function effectiveStatus(): string
    {
        if ($this->status === self::STATUS_ACTIVE && $this->expires_at->isPast()) {
            return self::STATUS_EXPIRED;
        }

        return $this->status;
    }

    public function isUsable(): bool
    {
        return $this->isActive() && $this->expires_at->isFuture();
    }

    public function hasCreatedTemporaryCredential(): bool
    {
        return $this->uses_temporary_credential
            && $this->temporary_credential_status === self::TEMPORARY_CREDENTIAL_CREATED
            && filled($this->temporary_username)
            && filled($this->temporary_password_encrypted);
    }
}
