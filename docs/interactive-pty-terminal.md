# Interactive SSH PTY Terminal

> **Status:** Experimental — authorised lab use only.

This feature adds an optional browser-based interactive SSH terminal for active JIT sessions, using xterm.js and a Ratchet-based WebSocket server.

---

## Quick Start

### 1. Start the WebSocket server

```bash
php artisan terminal:pty-server --port=8090
```

The server runs in the foreground. Stop with `Ctrl+C`.

### 2. Open the terminal

From any active JIT session detail page, click the **"Interactive Terminal"** button (marked with an `Exp` badge).

### 3. Use the terminal

The terminal connects via WebSocket to the Artisan server, which spawns an SSH PTY process to the target server. Type commands as you would in a normal SSH session. Type `exit` to close.

---

## Requirements

- PHP 8.3+ with `pcntl` extension (for signal handling)
- `ssh` binary on the server running the Artisan command
- `sshpass` binary (for password-based SSH authentication)
- `ratchet/rfc6455`, `react/socket`, `react/event-loop`, `react/child-process` (installed via Composer)
- Modern browser with WebSocket support

---

## Architecture

```
Browser (xterm.js)
    ↕ WebSocket (ws:// or wss:// via reverse proxy)
Artisan command: terminal:pty-server (port 8090)
    ↕ React\ChildProcess (non-blocking)
System ssh binary (ssh -tt user@host)
    ↕ SSH PTY
Target Linux Server
```

- **WebSocket protocol:** handled by `ratchet/rfc6455` (full RFC 6455 compliance: handshake, framing, masking, ping/pong, close frames)
- **SSH process:** managed by `react/child-process` for non-blocking I/O within the ReactPHP event loop
- **Concurrency:** multiple simultaneous terminal connections in one Artisan process

---

## Security

### Credential Handling

| Auth Type | Method | Secret Exposure |
|-----------|--------|-----------------|
| Password | `sshpass -e` with `SSHPASS` env var | Not in command line, `ps aux`, or `/proc/*/cmdline` |
| Private Key | Temp file with `chmod 0600` | Deleted after process close |

### Token Authentication

- HTTP route generates a **short-lived encrypted token** (5 minutes, AES-256-CBC via Laravel `Crypt`)
- Token payload: `jit_session_id`, `user_id`, `expires_at`, `nonce` (UUID)
- WebSocket client sends token as first message; server validates before spawning SSH
- Nonce tracked in-memory for single-use enforcement (lost on server restart — documented limitation)

### What Is NOT Logged

- Individual commands typed in the PTY shell
- SSH command strings containing credentials
- Passwords, private keys, or tokens in WebSocket messages

### What IS Logged (Audit Events)

| Event | When |
|-------|------|
| `interactive_terminal_opened` | SSH process spawned successfully |
| `interactive_terminal_closed` | SSH process ended |
| `interactive_terminal_failed` | SSH process failed to start |
| `interactive_terminal_denied` | Authorisation or token validation failed |

### Session Expiry Monitoring

The WebSocket server checks session status every 30 seconds. If the session expires or is revoked, the terminal is closed automatically.

---

## Known Limitations

1. **Command policy not enforced** — the interactive shell bypasses the `CommandPolicyService` blocklist. Use the SSH Command page for audited, policy-controlled commands.
2. **No command-level audit logging** — only terminal open/close events are logged. Keystroke/session recording is out of scope.
3. **Single-use nonce is in-memory only** — if the Artisan process restarts, nonces are lost and tokens within the 5-minute window could theoretically be reused.
4. **No terminal resize relay** — xterm.js resize events are sent as JSON but not yet applied to the remote PTY in this MVP.
5. **Requires `ssh` and `sshpass`** on the server running the Artisan command.
6. **No TLS by default** — use a reverse proxy (nginx/Caddy) for production.

---

## Reverse Proxy (Production)

### Nginx

```nginx
upstream pty_terminal_ws {
    server 127.0.0.1:8090;
}

location /ws/terminal {
    proxy_pass http://pty_terminal_ws;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_read_timeout 3600s;
    proxy_send_timeout 3600s;
}
```

### Cloudflare

- Enable WebSockets (on by default on all plans)
- Exclude `/ws/terminal` from caching rules
- Maximise WebSocket idle timeout

---

## Rollback

To remove this feature:

1. Stop the Artisan command
2. Delete the route line in `routes/web.php`
3. Revert the button in `resources/views/sessions/show.blade.php`
4. Delete these files:
   - `app/Console/Commands/PtyTerminalServer.php`
   - `app/Services/PtyTerminalService.php`
   - `app/Services/TerminalWebSocketHandler.php`
   - `app/Http/Controllers/TerminalController.php`
   - `resources/views/sessions/terminal.blade.php`
   - `tests/Feature/TerminalAccessControlTest.php`
5. Run `composer remove ratchet/rfc6455 react/socket react/event-loop react/stream react/child-process`
6. No database migrations or `.env` changes to revert.
