<?php

namespace Tests\Feature;

use App\Models\AccessRequest;
use App\Models\AuditLog;
use App\Models\JitSession;
use App\Models\TargetServer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TerminalAccessControlTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $otherUser;

    private TargetServer $server;

    private AccessRequest $accessRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'user']);
        $this->otherUser = User::factory()->create(['role' => 'user']);

        $this->server = TargetServer::create([
            'name' => 'Test Server',
            'host' => '10.0.0.1',
            'port' => 22,
            'ssh_username' => 'testuser',
            'auth_type' => 'password',
            'ssh_password_encrypted' => 'encrypted-value',
            'is_active' => true,
        ]);

        $this->accessRequest = AccessRequest::create([
            'user_id' => $this->owner->id,
            'target_server_id' => $this->server->id,
            'reason' => 'Testing terminal access',
            'requested_duration_minutes' => 60,
            'status' => AccessRequest::STATUS_APPROVED,
            'approved_by' => $this->owner->id,
            'approved_at' => now(),
        ]);
    }

    /**
     * Helper: create a usable active JIT session for the owner.
     */
    private function createUsableSession(?int $userId = null, bool $active = true): JitSession
    {
        return JitSession::create([
            'access_request_id' => $this->accessRequest->id,
            'user_id' => $userId ?? $this->owner->id,
            'target_server_id' => $this->server->id,
            'started_at' => now()->subMinutes(5),
            'expires_at' => now()->addHours(1),
            'status' => $active ? JitSession::STATUS_ACTIVE : JitSession::STATUS_EXPIRED,
        ]);
    }

    // ── Access Allowed ─────────────────────────────────────────────────────

    public function test_owner_can_access_terminal_page_for_active_session(): void
    {
        $session = $this->createUsableSession();

        $response = $this->actingAs($this->owner)
            ->get(route('sessions.terminal.show', $session));

        $response->assertOk();
        $response->assertViewIs('sessions.terminal');
        $response->assertViewHas('jitSession');
        $response->assertViewHas('terminalToken');
        $response->assertViewHas('wsUrl');
    }

    // ── Access Denied: Non-Owner ───────────────────────────────────────────

    public function test_non_owner_gets_403(): void
    {
        $session = $this->createUsableSession();

        $response = $this->actingAs($this->otherUser)
            ->get(route('sessions.terminal.show', $session));

        $response->assertForbidden();

        // Assert audit log created for denial
        $auditLog = AuditLog::where('action', 'interactive_terminal_denied')->first();
        $this->assertNotNull($auditLog);
        $this->assertEquals($this->otherUser->id, $auditLog->actor_id);
        $this->assertEquals('not_owner', $auditLog->metadata['reason'] ?? null);
    }

    // ── Access Denied: Expired Session ─────────────────────────────────────

    public function test_expired_session_gets_403(): void
    {
        $session = $this->createUsableSession(active: false);

        $response = $this->actingAs($this->owner)
            ->get(route('sessions.terminal.show', $session));

        $response->assertForbidden();

        $auditLog = AuditLog::where('action', 'interactive_terminal_denied')
            ->where('metadata->reason', 'session_not_usable')
            ->first();
        $this->assertNotNull($auditLog);
    }

    // ── Access Denied: Revoked Session ─────────────────────────────────────

    public function test_revoked_session_gets_403(): void
    {
        $session = $this->createUsableSession();
        $session->update([
            'status' => JitSession::STATUS_REVOKED,
            'revoked_at' => now(),
            'revoked_by' => $this->owner->id,
            'revoke_reason' => 'Testing',
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('sessions.terminal.show', $session));

        $response->assertForbidden();
    }

    // ── Access Denied: Inactive Target Server ──────────────────────────────

    public function test_inactive_target_server_gets_403(): void
    {
        $this->server->update(['is_active' => false]);
        $session = $this->createUsableSession();

        $response = $this->actingAs($this->owner)
            ->get(route('sessions.terminal.show', $session));

        $response->assertForbidden();

        $auditLog = AuditLog::where('action', 'interactive_terminal_denied')
            ->where('metadata->reason', 'server_inactive')
            ->first();
        $this->assertNotNull($auditLog);
    }

    // ── Access Denied: Unauthenticated ─────────────────────────────────────

    public function test_guest_is_redirected_to_login(): void
    {
        $session = $this->createUsableSession();

        $response = $this->get(route('sessions.terminal.show', $session));

        $response->assertRedirect(route('login'));
    }

    // ── Token Generation ───────────────────────────────────────────────────

    public function test_terminal_page_contains_encrypted_token(): void
    {
        $session = $this->createUsableSession();

        $response = $this->actingAs($this->owner)
            ->get(route('sessions.terminal.show', $session));

        $response->assertOk();

        // Token should be a non-empty encrypted string
        $token = $response->viewData('terminalToken');
        $this->assertNotEmpty($token);
        $this->assertIsString($token);

        // WS URL should start with ws:// or wss://
        $wsUrl = $response->viewData('wsUrl');
        $this->assertMatchesRegularExpression('/^wss?:\/\//', $wsUrl);
    }

    // ── Route Exists ───────────────────────────────────────────────────────

    public function test_terminal_route_exists(): void
    {
        $session = $this->createUsableSession();

        // Verify the route is registered
        $this->assertTrue(
            app('router')->has('sessions.terminal.show'),
            'Route sessions.terminal.show should be registered.'
        );
    }
}
