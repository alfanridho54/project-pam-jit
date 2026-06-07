<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'jit_session_id',
    'user_id',
    'target_server_id',
    'command',
    'status',
    'output_excerpt',
    'exit_code',
    'executed_at',
])]
class CommandLog extends Model
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';
    public const STATUS_BLOCKED = 'blocked';
    public const STATUS_DENIED = 'denied';

    /**
     * @return array<int, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_SUCCESS,
            self::STATUS_FAILED,
            self::STATUS_BLOCKED,
            self::STATUS_DENIED,
        ];
    }

    protected function casts(): array
    {
        return [
            'executed_at' => 'datetime',
        ];
    }

    public function jitSession(): BelongsTo
    {
        return $this->belongsTo(JitSession::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function targetServer(): BelongsTo
    {
        return $this->belongsTo(TargetServer::class);
    }
}
