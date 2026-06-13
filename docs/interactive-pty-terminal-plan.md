# Interactive SSH PTY Terminal — Implementation Plan (Rev 3 — Implemented)

> **Note:** `cboden/ratchet` is incompatible with Symfony 7 (Laravel 13). Implementation uses `ratchet/rfc6455` (the underlying protocol library) + ReactPHP directly. Same RFC 6455 compliance, same architecture, just without the Ratchet wrapper that depends on `symfony/http-foundation`.

## 1. Overview

Add an **optional, isolated** interactive browser-based terminal for active JIT sessions. Users can open a real SSH PTY shell in their browser via xterm.js, proxied through a **Ratchet** WebSocket server running inside a Laravel Artisan command. This complements (does not replace) the existing SSH command execution page.

---

## 2. Why Ratchet (Not Hand-Written WebSocket)

### The Problem with Raw `stream_socket_server()`

A WebSocket connection is **not** a raw TCP socket. The WebSocket protocol (RFC 6455) requires:

1. **HTTP Upgrade Handshake** — The server must parse the client's `Upgrade: websocket` HTTP request, validate `Sec-WebSocket-Key`, compute a SHA-1 response hash, and return a valid `101 Switching Protocols` response. A malformed handshake must be rejected.
2. **Frame Encoding/Decoding** — All data is sent in binary frames with a specific structure: FIN bit, opcode, payload length (7-bit, 16-bit, or 64-bit), masking key, and masked payload. The server must correctly decode client→server frames (always masked) and encode server→client frames (never masked).
3. **Frame Masking/Unmasking** — Client frames are XOR-masked with a 4-byte key. Incorrect unmasking produces garbage data and silent corruption.
4. **Fragmentation** — Large messages can be split across multiple frames. The server must reassemble them.
5. **Control Frames** — Ping, Pong, and Close frames have specific semantics. Ping must be answered with Pong. Close frames trigger a closing handshake. Missing these causes browser timeouts, zombie connections, and proxy failures.
6. **Browser Compatibility** — Chrome, Firefox, Safari, and Edge all have strict expectations. Any protocol deviation causes silent connection drops with no useful error message.

Writing all of this correctly in ~200–400 lines of raw PHP is **possible but risky**:
- A single bit-manipulation bug in frame decoding causes silent data corruption
- Missing ping/pong causes connections to die behind Cloudflare/Nginx after 60 seconds
- Missing close handshake leaves orphan SSH processes
- Very difficult to test thoroughly
- Security: incorrect frame parsing could be exploited for cache poisoning or smuggling attacks

### Why Ratchet Is Safer

[Ratchet](http://socketo.me/) (`cboden/ratchet`) is a mature, widely-used PHP WebSocket library that:

- Fully implements RFC 6455 (handshake, framing, masking, fragmentation, control frames)
- Has been battle-tested since 2012 across thousands of production deployments
- Provides a clean `MessageComponentInterface` — you handle `onOpen`, `onMessage`, `onClose`, `onError`; the library handles protocol details
- Uses ReactPHP's event loop for non-blocking I/O (efficient multi-connection handling)
- Handles edge cases: partial reads, buffer overflow protection, connection timeouts
- Compatible with all modern browsers without custom protocol code

### Exact Composer Dependency

```
composer require cboden/ratchet
```

This pulls in approximately:
- `cboden/ratchet` — WebSocket server library
- `ratchet/rfc6455` — WebSocket protocol implementation
- `react/socket` — async TCP socket
- `react/event-loop` — event loop
- `react/stream` — stream abstractions
- `guzzlehttp/psr7` — HTTP message parsing (for handshake)

All are well-maintained, widely-used packages. Total install size is small (~2 MB).

---

## 3. Architecture

```
Browser (xterm.js)
    ↕ WebSocket (wss:// via reverse proxy, or ws:// locally)
Ratchet WebSocket Server (Artisan: terminal:pty-server)
    ↕ ReactPHP event loop + proc_open() pipes
System ssh binary (ssh -tt user@host -p port)
    ↕ SSH PTY
Target Linux Server
```

### Data Flow

1. User clicks "Open Interactive Terminal" → browser loads `terminal.blade.php`
2. xterm.js opens WebSocket to Ratchet server (port 8090 by default)
3. Client sends auth token as first message
4. Server validates token, resolves SSH credentials, spawns `proc_open()` with `ssh -tt`
5. ReactPHP `ChildProcess` or periodic timer reads SSH stdout/stderr → sends to browser as WebSocket text frames
6. Browser keystrokes arrive as WebSocket text frames → written to SSH stdin pipe
7. On session expiry/revocation, disconnect, or `exit`: kill SSH process, close WebSocket

### Concurrency Model

- Ratchet runs on ReactPHP's event loop (single-threaded, non-blocking)
- `proc_open()` creates the SSH child process; pipes are set to non-blocking mode
- ReactPHP `Stream` reads from stdout/stderr pipes without blocking the event loop
- Multiple concurrent terminal sessions are handled in one Artisan process

---

## 4. Files to Create / Modify

### New Files

| File | Purpose |
|------|---------|
| `app/Console/Commands/PtyTerminalServer.php` | Artisan command `terminal:pty-server` — starts Ratchet WebSocket server |
| `app/Services/PtyTerminalService.php` | Service class — SSH credential resolution, `proc_open()` management, process lifecycle |
| `app/Services/TerminalWebSocketHandler.php` | Ratchet `MessageComponentInterface` implementation — handles WebSocket events, bridges to SSH process |
| `app/Http/Controllers/TerminalController.php` | Controller — authorization, terminal page rendering, token generation |
| `resources/views/sessions/terminal.blade.php` | Blade view — xterm.js terminal UI with WebSocket client |
| `docs/interactive-pty-terminal.md` | User-facing documentation |
| `tests/Feature/TerminalAccessControlTest.php` | HTTP access control tests |

### Modified Files

| File | Change |
|------|--------|
| `composer.json` | Add `cboden/ratchet` dependency via `composer require` |
| `routes/web.php` | Add `GET /sessions/{jitSession}/terminal` route |
| `resources/views/sessions/show.blade.php` | Add "Open Interactive Terminal" button (conditional) |

### Files NOT Changed

- `app/Services/SshCommandService.php` — untouched
- `app/Services/SshConnectionService.php` — untouched
- `app/Services/TemporaryLinuxCredentialService.php` — untouched
- `app/Services/CommandPolicyService.php` — untouched
- `app/Console/Commands/JitSessionsMonitor.php` — untouched
- `app/Services/ProxmoxService.php` — untouched
- All existing controllers, models, migrations, notifications — untouched
- `.env` — untouched
- No new database migrations

---

## 5. New Route

```php
// In routes/web.php, inside the auth+verified middleware group:
Route::get('/sessions/{jitSession}/terminal', [TerminalController::class, 'show'])
    ->name('sessions.terminal.show');
```

Only one HTTP route. The WebSocket connection is handled by the Ratchet Artisan process on a separate port (default 8090), not through Laravel's HTTP router.

---

## 6. Credential Handling

### 6.1 Credential Resolution

`PtyTerminalService` resolves credentials server-side (never sent to browser):
- If session has temporary credential → decrypt `temporary_password_encrypted`, use `temporary_username`
- Else → use target server's stored credential (same logic as `SshCommandService::loginFor()`)

### 6.2 Password Authentication — `sshpass -e`

When password auth is required:

```php
// Build environment for the child process
$env = [
    'SSHPASS' => $decryptedPassword,  // passed via environment, NOT command line
];

$command = sprintf(
    'sshpass -e ssh -tt -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -p %d %s@%s',
    $port,
    escapeshellarg($username),
    escapeshellarg($host)
);

$process = proc_open($command, $descriptors, $pipes, null, $env);
```

Key rules:
- **`sshpass -e`** reads password from the `SSHPASS` environment variable — never appears in `/proc/*/cmdline`, `ps aux`, or shell history
- Password is **not** part of the command string
- The generated command string is **never logged** — only opaque references like `"ssh session for JIT #42"` appear in logs
- After `proc_open()`, the `$env` array is unset immediately

### 6.3 Private Key Authentication — Temporary Key File

When private key auth is required:

```php
// Write decrypted key to a temporary file
$tempKeyPath = tempnam(sys_get_temp_dir(), 'pam_jit_key_');
file_put_contents($tempKeyPath, $decryptedPrivateKey);
chmod($tempKeyPath, 0600);  // owner-read-only

$command = sprintf(
    'ssh -tt -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -i %s -p %d %s@%s',
    escapeshellarg($tempKeyPath),
    $port,
    escapeshellarg($username),
    escapeshellarg($host)
);

// After SSH process exits (in cleanup):
@unlink($tempKeyPath);
```

Key rules:
- Temporary key file has `0600` permissions (SSH requires this)
- File is created in system temp directory with a random prefix
- File is **always deleted** in a cleanup handler, even on error/exception
- Decrypted key content is **never logged**
- `$tempKeyPath` is tracked per-connection and cleaned up on WebSocket close

### 6.4 Security Summary

| Concern | Mitigation |
|---------|------------|
| Password in process list | `sshpass -e` uses env var, not CLI arg |
| Private key exposure | Temp file `0600`, deleted on close |
| Secrets in logs | Command string never logged; only opaque session references |
| Secrets in WebSocket | Only terminal I/O bytes traverse WebSocket |
| Secrets in audit metadata | Only `jit_session_id`, `target_server_id`, `duration_seconds` |
| Secrets in errors | Error messages are generic ("SSH authentication failed") |

---

## 7. Security Design

### 7.1 Authorization

**Route-level** — `TerminalController@show` checks:
1. User is authenticated
2. `jitSession->user_id === auth()->id()` (owner only)
3. `jitSession->isUsable()` is true
4. `jitSession->targetServer->is_active` is true
5. Target server has valid SSH credential or session has temporary credential

If any check fails → abort(403) or redirect with error + audit `interactive_terminal_denied`.

### 7.2 WebSocket Token

- `TerminalController@show` generates a **short-lived signed token** using `Crypt::encrypt()`
- Token payload: `jit_session_id`, `user_id`, `expires_at` (now + 5 minutes), `nonce` (random UUID)
- Token is embedded in the Blade view as a JavaScript variable
- WebSocket client sends token as first message on connect
- Server validates token: decrypt, check expiry, check user_id matches, check nonce not already used
- Token is **single-use**: nonce is stored in an in-memory set in the Ratchet server; once consumed, it cannot be reused
- If validation fails → WebSocket connection closed immediately + audit `interactive_terminal_denied`

### 7.3 Session Expiry / Revocation Monitoring

- Ratchet handler sets a ReactPHP periodic timer (every 30 seconds per connection)
- Timer queries `JitSession::find($id)->isUsable()` from database
- If session becomes non-usable → send `{"type":"close","reason":"session_expired"}` to client, kill SSH process, close WebSocket
- Client displays "Session expired — terminal closed" message

### 7.4 Process Isolation

- Each terminal WebSocket connection spawns exactly one `proc_open()` SSH process
- Process is killed when:
  - WebSocket connection drops (`onClose`)
  - Session expires/is revoked (periodic check)
  - User types `exit` or SSH session ends naturally (stdout EOF detected)
  - Artisan command receives SIGTERM (graceful shutdown handler)
- All SSH processes are tracked in an in-memory map: `connectionId → process resource`
- On Artisan shutdown, all tracked processes receive SIGTERM, then SIGKILL after 5 seconds

---

## 8. Audit Events

Recorded via existing `AuditLogService`:

| Action | When |
|--------|------|
| `interactive_terminal_opened` | WebSocket connected, SSH process spawned successfully |
| `interactive_terminal_closed` | SSH process ended (normal exit, timeout, or session expiry) |
| `interactive_terminal_failed` | SSH process failed to start (auth failure, connection error) |
| `interactive_terminal_denied` | Authorization or token validation failed |

Metadata (no secrets):
```php
[
    'jit_session_id' => $session->id,
    'target_server_id' => $session->target_server_id,
    'duration_seconds' => $duration, // on close only
]
```

Note: `AuditLogService` requires a Laravel `request()` context. For WebSocket-side audit calls (where there is no HTTP request), we will either:
- Pass the original HTTP request IP/user-agent captured during token generation, or
- Accept that `ip_address`/`user_agent` may be null for WebSocket-originated audit entries.

---

## 9. Artisan Command Lifecycle

### Starting the Server

```bash
php artisan terminal:pty-server --port=8090 --host=0.0.0.0
```

- Runs in foreground (attach to tmux/screen, or use systemd/supervisor in production)
- Prints connection events to stdout for debugging
- Graceful shutdown on SIGTERM/SIGINT: closes all WebSocket connections, kills all SSH processes, then exits

### Stopping the Server

- `Ctrl+C` (SIGINT) — graceful shutdown
- `kill <pid>` (SIGTERM) — graceful shutdown
- `kill -9 <pid>` — force kill (SSH processes may linger briefly until OS cleans up pipes)

### Production Deployment (Future)

For production, wrap in a systemd service:

```ini
[Unit]
Description=PAM JIT PTY Terminal WebSocket Server
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/pam-jit
ExecStart=/usr/bin/php /var/www/pam-jit/artisan terminal:pty-server --port=8090 --host=127.0.0.1
Restart=on-failure
RestartSec=5

[Install]
WantedBy=multi-user.target
```

### If Terminal Feature Is Not Needed

Simply **do not start** the Artisan command. The button on the session page will show a "Terminal server unavailable" message if the WebSocket port is not reachable. Zero impact on the rest of the application.

---

## 10. Reverse Proxy / TLS (Future Production Setup)

### Nginx Proxy

```nginx
upstream pty_terminal_ws {
    server 127.0.0.1:8090;
}

server {
    # ... existing server block ...

    location /ws/terminal {
        proxy_pass http://pty_terminal_ws;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 3600s;   # keep-alive for long sessions
        proxy_send_timeout 3600s;
    }
}
```

### Cloudflare

- Enable WebSockets in Cloudflare dashboard (enabled by default on all plans)
- Ensure the WebSocket path (`/ws/terminal`) is not cached
- Cloudflare will handle TLS termination; Nginx proxies plain `ws://` to the Ratchet server
- Set Cloudflare's "Websocket idle timeout" to maximum (typically 100–300 seconds on free plans; longer on paid plans)

### Path-Based Routing (Alternative)

Instead of a separate port, Nginx can route `/ws/terminal` to the Ratchet server while routing everything else to PHP-FPM. This means the browser only needs one origin (same hostname + port), simplifying CORS and CSP.

---

## 11. Frontend (xterm.js)

- Load xterm.js + xterm-addon-fit via CDN (`<script>` and `<link>` tags in Blade view)
- Dark terminal theme, responsive sizing via FitAddon
- Connection status indicator: connecting / connected / disconnected
- Auto-reconnect disabled — user must re-open from session page
- "Close Terminal" button cleanly disconnects WebSocket
- On WebSocket close, display reason (normal, session expired, server error)

---

## 12. Known Limitations

1. **Command policy not enforced** — Interactive PTY gives a full shell. The existing `CommandPolicyService` blocklist cannot intercept keystrokes. Documented trade-off.
2. **Command logs incomplete** — Individual commands typed in the PTY are NOT logged to `command_logs`. Only terminal open/close events are audited. Full session recording is out of scope for MVP.
3. **No clipboard integration** — xterm.js clipboard is browser-only.
4. **Single terminal per session** — One concurrent WebSocket connection per JIT session.
5. **Requires system binaries** — Server must have `ssh` and `sshpass` installed.
6. **Artisan command must be running** — Terminal is unavailable if `terminal:pty-server` is not started.
7. **No TLS on WebSocket by default** — Use reverse proxy for production.
8. **Ratchet adds a dependency** — ~6 packages, ~2 MB. Mitigated by easy rollback.
9. **No Windows support** — `proc_open()` with `ssh -tt` requires a Unix-like environment.

---

## 13. Manual Test Checklist

### Prerequisites
- [ ] `cboden/ratchet` installed via Composer
- [ ] `ssh` and `sshpass` available on server (`which ssh sshpass`)
- [ ] Active JIT session exists
- [ ] `terminal:pty-server` Artisan command is running

### Access Control
- [ ] Session owner → `/sessions/{id}/terminal` loads
- [ ] Non-owner → 403
- [ ] Expired session → denied
- [ ] Revoked session → denied
- [ ] Closed session → denied
- [ ] Unauthenticated → redirect to login

### Terminal Functionality
- [ ] "Open Interactive Terminal" button visible only when session is usable
- [ ] Terminal connects and shows shell prompt
- [ ] Commands typed produce output
- [ ] `exit` closes terminal cleanly
- [ ] Closing browser tab kills SSH process
- [ ] Resize terminal → xterm.js sends resize frame (optional, may not resize remote PTY in MVP)

### Session Lifecycle
- [ ] Revoke while terminal open → terminal closes within 30s
- [ ] Session expires while terminal open → terminal closes within 30s

### Credential Handling
- [ ] Password auth: `sshpass -e` used, password not in `ps aux` output
- [ ] Private key auth: temp key file created with 0600, deleted after close
- [ ] No passwords/keys in Laravel logs, audit metadata, or WebSocket frames

### Audit Logs
- [ ] `interactive_terminal_opened` logged on connect
- [ ] `interactive_terminal_closed` logged on disconnect
- [ ] `interactive_terminal_denied` logged on auth failure
- [ ] No secrets in audit metadata

### Non-Regression
- [ ] Existing SSH command execution page works
- [ ] SFTP profile download works
- [ ] Session revoke/expire flow unchanged
- [ ] `php artisan route:list` shows only the one new route
- [ ] `php artisan test` passes

---

## 14. Rollback Plan

### Full Feature Removal

1. **Stop the Artisan command:** `Ctrl+C` or `kill` the `terminal:pty-server` process
2. **Remove the route:** Delete the one line in `routes/web.php`
3. **Remove the button:** Revert `resources/views/sessions/show.blade.php`
4. **Delete new files:**
   - `app/Console/Commands/PtyTerminalServer.php`
   - `app/Services/PtyTerminalService.php`
   - `app/Services/TerminalWebSocketHandler.php`
   - `app/Http/Controllers/TerminalController.php`
   - `resources/views/sessions/terminal.blade.php`
   - `tests/Feature/TerminalAccessControlTest.php`
   - `docs/interactive-pty-terminal.md`
5. **Remove Composer dependency:**
   ```bash
   composer remove cboden/ratchet
   ```
   This also removes `ratchet/rfc6455`, `react/socket`, `react/event-loop`, `react/stream`, and any other transitive dependencies that are no longer needed.
6. **No database changes** — no migrations; audit logs use existing `audit_logs` table
7. **No `.env` changes**
8. **Rebuild frontend:** `npm run build` (if xterm.js was installed via npm; skip if CDN was used)

### Rollback Impact

| Area | Impact |
|------|--------|
| Database | None |
| `.env` | None |
| Existing features | None |
| Composer lock | Reverted to pre-feature state |
| Total files deleted | 7 new files + 3 file reverts |

### Partial Rollback (Disable Without Removing)

- Simply stop the `terminal:pty-server` Artisan command
- The button will show "Terminal server unavailable"
- No code changes needed

---

## 15. Implementation Order (if approved)

1. `composer require cboden/ratchet`
2. Create `PtyTerminalService` — credential resolution, `proc_open()` SSH process management, temp key file handling
3. Create `TerminalWebSocketHandler` — Ratchet `MessageComponentInterface` with SSH process bridging
4. Create `PtyTerminalServer` Artisan command — boots Ratchet with the handler
5. Create `TerminalController` — authorization, page rendering, token generation
6. Add route in `routes/web.php`
7. Create `terminal.blade.php` — xterm.js view with WebSocket client
8. Add "Open Interactive Terminal" button to `show.blade.php`
9. Add audit logging in handler/controller
10. Create `TerminalAccessControlTest` — HTTP access control tests
11. Create `docs/interactive-pty-terminal.md` — user documentation
12. Run verification: `php artisan route:list`, `php artisan view:cache`, `php -l` on all new PHP files, `php artisan test`
