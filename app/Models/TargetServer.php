<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'host',
    'port',
    'ssh_username',
    'auth_type',
    'ssh_password_encrypted',
    'ssh_private_key_encrypted',
    'description',
    'is_active',
    'last_health_status',
    'last_health_checked_at',
    'last_health_latency_ms',
    'last_health_message',
    'last_jit_readiness_status',
    'last_jit_readiness_checked_at',
    'last_jit_readiness_message',
    'last_jit_readiness_details',
])]
#[Hidden(['ssh_password_encrypted', 'ssh_private_key_encrypted'])]
class TargetServer extends Model
{
    protected function casts(): array
    {
        return [
            'is_active'                    => 'boolean',
            'last_health_checked_at'       => 'datetime',
            'last_health_latency_ms'       => 'integer',
            'last_jit_readiness_checked_at' => 'datetime',
            'last_jit_readiness_details'   => 'array',
        ];
    }

    public function hasPassword(): bool
    {
        return filled($this->ssh_password_encrypted);
    }

    public function hasPrivateKey(): bool
    {
        return filled($this->ssh_private_key_encrypted);
    }

    // ── Health Check helpers ────────────────────────────────────────────────

    /**
     * Human-readable label for the last health status.
     */
    public function healthStatusLabel(): string
    {
        return match ($this->last_health_status) {
            'ssh_ok'      => 'SSH OK',
            'tcp_open'    => 'TCP Open',
            'tcp_failed'  => 'TCP Failed',
            'ssh_failed'  => 'SSH Failed',
            'unreachable' => 'Unreachable',
            'error'       => 'Error',
            'online'      => 'Online',
            default       => 'Unknown',
        };
    }

    /**
     * Badge variant string for use with x-badge or Blade class selection.
     */
    public function healthStatusBadgeVariant(): string
    {
        return match ($this->last_health_status) {
            'ssh_ok', 'online'  => 'health-ok',
            'tcp_open'          => 'health-tcp',
            'tcp_failed',
            'ssh_failed',
            'unreachable',
            'error'             => 'health-fail',
            default             => 'health-unknown',
        };
    }

    /**
     * Returns true if the last health check was successful within the last 24 hours.
     */
    public function wasRecentlyHealthy(): bool
    {
        return in_array($this->last_health_status, ['ssh_ok', 'tcp_open', 'online'], true)
            && $this->last_health_checked_at !== null
            && $this->last_health_checked_at->isAfter(now()->subHours(24));
    }

    // ── JIT Readiness helpers ───────────────────────────────────────────

    /**
     * Human-readable label for the last JIT readiness status.
     */
    public function jitReadinessStatusLabel(): string
    {
        return match ($this->last_jit_readiness_status) {
            'ready'     => 'Ready',
            'not_ready' => 'Not Ready',
            'ssh_failed' => 'SSH Failed',
            'error'     => 'Error',
            'unknown'   => 'Unknown',
            default     => 'Unknown',
        };
    }

    /**
     * Badge variant string for JIT readiness status.
     */
    public function jitReadinessBadgeVariant(): string
    {
        return match ($this->last_jit_readiness_status) {
            'ready'     => 'readiness-ok',
            'not_ready',
            'ssh_failed',
            'error'     => 'readiness-fail',
            default     => 'readiness-unknown',
        };
    }

    // ── Relationships ────────────────────────────────────────────────────────

    public function accessRequests(): HasMany
    {
        return $this->hasMany(AccessRequest::class);
    }

    public function jitSessions(): HasMany
    {
        return $this->hasMany(JitSession::class);
    }

    public function commandLogs(): HasMany
    {
        return $this->hasMany(CommandLog::class);
    }
}
