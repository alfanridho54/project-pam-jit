<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\TargetServer;
use App\Models\User;
use App\Services\TargetServerJitReadinessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class TargetServerJitReadinessTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $regularUser;
    private TargetServer $server;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->regularUser = User::factory()->create([
            'role' => 'user',
        ]);

        $this->server = TargetServer::create([
            'name' => 'Test Server',
            'host' => '127.0.0.1',
            'port' => 22,
            'ssh_username' => 'test-user',
            'auth_type' => 'password',
            'ssh_password_encrypted' => 'encrypted-secret-here',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_run_readiness_check_with_ready_result(): void
    {
        $checkedAt = now();

        $this->mock(TargetServerJitReadinessService::class, function ($mock) use ($checkedAt) {
            $mock->shouldReceive('check')
                ->once()
                ->with(Mockery::on(fn($server) => $server instanceof TargetServer && $server->id === $this->server->id))
                ->andReturn([
                    'status' => 'ready',
                    'message' => 'Target server is ready for JIT temporary credential provisioning.',
                    'checkedAt' => $checkedAt,
                    'details' => [
                        'sudo_id_ok' => true,
                        'useradd_ok' => true,
                        'chpasswd_ok' => true,
                        'usermod_ok' => true,
                        'userdel_ok' => true,
                    ],
                ]);
        });

        $response = $this->actingAs($this->admin)
            ->from(route('admin.target-servers.edit', $this->server))
            ->post(route('admin.target-servers.jit-readiness-check', $this->server));

        $response->assertRedirect(route('admin.target-servers.edit', $this->server));
        $response->assertSessionHas('success', 'JIT readiness check: Target server is ready for JIT temporary credential provisioning.');

        // Assert target server model fields updated
        $this->server->refresh();
        $this->assertEquals('ready', $this->server->last_jit_readiness_status);
        $this->assertEquals($checkedAt->toDateTimeString(), $this->server->last_jit_readiness_checked_at?->toDateTimeString());
        $this->assertEquals('Target server is ready for JIT temporary credential provisioning.', $this->server->last_jit_readiness_message);
        $this->assertIsArray($this->server->last_jit_readiness_details);
        $this->assertTrue($this->server->last_jit_readiness_details['sudo_id_ok']);
        $this->assertTrue($this->server->last_jit_readiness_details['useradd_ok']);

        // Assert helper methods
        $this->assertEquals('Ready', $this->server->jitReadinessStatusLabel());
        $this->assertEquals('readiness-ok', $this->server->jitReadinessBadgeVariant());

        // Assert audit log created
        $auditLog = AuditLog::where('action', 'target_server_jit_readiness_ready')->first();
        $this->assertNotNull($auditLog);
        $this->assertEquals($this->admin->id, $auditLog->actor_id);
        $this->assertEquals($this->server->id, $auditLog->target_id);
        $this->assertEquals('TargetServer', $auditLog->target_type);
        $this->assertEquals($this->server->id, $auditLog->metadata['target_server_id']);
        $this->assertEquals('ready', $auditLog->metadata['status']);
    }

    public function test_admin_can_run_readiness_check_with_not_ready_result(): void
    {
        $checkedAt = now();

        $this->mock(TargetServerJitReadinessService::class, function ($mock) use ($checkedAt) {
            $mock->shouldReceive('check')
                ->once()
                ->with(Mockery::on(fn($server) => $server instanceof TargetServer && $server->id === $this->server->id))
                ->andReturn([
                    'status' => 'not_ready',
                    'message' => 'SSH login works, but sudo user management commands are not ready.',
                    'checkedAt' => $checkedAt,
                    'details' => [
                        'sudo_id_ok' => true,
                        'useradd_ok' => false,
                        'chpasswd_ok' => false,
                        'usermod_ok' => false,
                        'userdel_ok' => false,
                    ],
                ]);
        });

        $response = $this->actingAs($this->admin)
            ->from(route('admin.target-servers.index'))
            ->post(route('admin.target-servers.jit-readiness-check', $this->server));

        $response->assertRedirect(route('admin.target-servers.index'));
        $response->assertSessionHas('warning', 'JIT readiness check: SSH login works, but sudo user management commands are not ready.');

        // Assert target server model fields updated
        $this->server->refresh();
        $this->assertEquals('not_ready', $this->server->last_jit_readiness_status);
        $this->assertEquals($checkedAt->toDateTimeString(), $this->server->last_jit_readiness_checked_at?->toDateTimeString());
        $this->assertIsArray($this->server->last_jit_readiness_details);
        $this->assertTrue($this->server->last_jit_readiness_details['sudo_id_ok']);
        $this->assertFalse($this->server->last_jit_readiness_details['useradd_ok']);

        // Assert helper methods
        $this->assertEquals('Not Ready', $this->server->jitReadinessStatusLabel());
        $this->assertEquals('readiness-fail', $this->server->jitReadinessBadgeVariant());

        // Assert audit log created
        $auditLog = AuditLog::where('action', 'target_server_jit_readiness_not_ready')->first();
        $this->assertNotNull($auditLog);
        $this->assertEquals('not_ready', $auditLog->metadata['status']);
    }

    public function test_regular_user_cannot_run_readiness_check(): void
    {
        $response = $this->actingAs($this->regularUser)
            ->post(route('admin.target-servers.jit-readiness-check', $this->server));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->post(route('admin.target-servers.jit-readiness-check', $this->server));

        $response->assertRedirect(route('login'));
    }
}
