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
])]
#[Hidden(['ssh_password_encrypted', 'ssh_private_key_encrypted'])]
class TargetServer extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
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

    public function accessRequests(): HasMany
    {
        return $this->hasMany(AccessRequest::class);
    }

    public function jitSessions(): HasMany
    {
        return $this->hasMany(JitSession::class);
    }
}
