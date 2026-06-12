<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\TargetServer;
use App\Models\User;
use App\Services\TargetServerHealthCheckService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TargetServerHealthCheckTest extends TestCase
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

    public function test_admin_can_run_health_check_with_success(): void
    {
        $checkedAt = now();

        $this->mock(TargetServerHealthCheckService::class, function ($mock) use ($checkedAt) {
            $mock->shouldReceive('check')
                ->once()
                ->with(fn($server) => $server->id === $this->server->id)
                ->andReturn([
                    'status' => 'ssh_ok',
                    'tcpOk' => true,
                    'sshOk' => true,
                    'latencyMs' => 15,
                    'message' => 'TCP connection and SSH authentication both succeeded.',
                    'checkedAt' => $checkedAt,
                ]);
        });

        $response = $this->actingAs($this->admin)
            ->from(route('admin.target-servers.index'))
            ->post(route('admin.target-servers.health-check', $this->server));

        $response->assertRedirect(route('admin.target-servers.index'));
        $response->assertSessionHas('success', 'Health check complete: TCP connection and SSH authentication both succeeded.');

        // Assert target server model fields updated
        $this->server->refresh();
        $this->assertEquals('ssh_ok', $this->server->last_health_status);
        $this->assertEquals($checkedAt->toDateTimeString(), $this->server->last_health_checked_at?->toDateTimeString());
        $this->assertEquals(15, $this->server->last_health_latency_ms);
        $this->assertEquals('TCP connection and SSH authentication both succeeded.', $this->server->last_health_message);

        // Assert helper methods
        $this->assertEquals('SSH OK', $this->server->healthStatusLabel());
        $this->assertEquals('health-ok', $this->server->healthStatusBadgeVariant());
        $this->assertTrue($this->server->wasRecentlyHealthy());

        // Assert audit log created
        $auditLog = AuditLog::where('action', 'target_server_health_check_succeeded')->first();
        $this->assertNotNull($auditLog);
        $this->assertEquals($this->admin->id, $auditLog->actor_id);
        $this->assertEquals($this->server->id, $auditLog->target_id);
        $this->assertEquals('TargetServer', $auditLog->target_type);
        $this->assertEquals($this->server->id, $auditLog->metadata['target_server_id']);
        $this->assertEquals('ssh_ok', $auditLog->metadata['status']);
    }

    public function test_admin_can_run_health_check_with_failure(): void
    {
        $checkedAt = now();

        $this->mock(TargetServerHealthCheckService::class, function ($mock) use ($checkedAt) {
            $mock->shouldReceive('check')
                ->once()
                ->with(fn($server) => $server->id === $this->server->id)
                ->andReturn([
                    'status' => 'tcp_failed',
                    'tcpOk' => false,
                    'sshOk' => false,
                    'latencyMs' => null,
                    'message' => 'TCP connection to port 22 failed or timed out. Connection refused.',
                    'checkedAt' => $checkedAt,
                ]);
        });

        $response = $this->actingAs($this->admin)
            ->from(route('admin.target-servers.edit', $this->server))
            ->post(route('admin.target-servers.health-check', $this->server));

        $response->assertRedirect(route('admin.target-servers.edit', $this->server));
        $response->assertSessionHas('warning', 'Health check complete: TCP connection to port 22 failed or timed out. Connection refused.');

        // Assert target server model fields updated
        $this->server->refresh();
        $this->assertEquals('tcp_failed', $this->server->last_health_status);
        $this->assertEquals($checkedAt->toDateTimeString(), $this->server->last_health_checked_at?->toDateTimeString());
        $this->assertNull($this->server->last_health_latency_ms);
        $this->assertEquals('TCP connection to port 22 failed or timed out. Connection refused.', $this->server->last_health_message);

        // Assert helper methods
        $this->assertEquals('TCP Failed', $this->server->healthStatusLabel());
        $this->assertEquals('health-fail', $this->server->healthStatusBadgeVariant());
        $this->assertFalse($this->server->wasRecentlyHealthy());

        // Assert audit log created
        $auditLog = AuditLog::where('action', 'target_server_health_check_failed')->first();
        $this->assertNotNull($auditLog);
        $this->assertEquals($this->admin->id, $auditLog->actor_id);
        $this->assertEquals('tcp_failed', $auditLog->metadata['status']);
    }

    public function test_regular_user_cannot_run_health_check(): void
    {
        $response = $this->actingAs($this->regularUser)
            ->post(route('admin.target-servers.health-check', $this->server));

        $response->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->post(route('admin.target-servers.health-check', $this->server));

        $response->assertRedirect(route('login'));
    }
}
