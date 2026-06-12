# Target Server Health Check

## Overview

The **Target Server Health Check** feature allows admin users to verify whether a target server is reachable before approving an access request or troubleshooting a JIT session.

The check is **non-destructive** and **read-only**: it opens a brief TCP socket to measure reachability and latency, and optionally attempts SSH authentication using stored encrypted credentials. No temporary credentials are created or modified.

---

## How It Works

### 1. TCP Connectivity Check

A raw TCP socket is opened to `host:port` with a **3-second timeout**.

- If the socket opens successfully, the server is reachable at the network layer.
- If it fails (timeout or refused), the check stops with `tcp_failed`.
- Round-trip latency (in milliseconds) is recorded.

### 2. SSH Authentication Check (Optional)

If TCP succeeds **and** the target server has:
- A configured `ssh_username`, **and**
- A stored credential (`password` or `private_key`) matching its `auth_type`

…then an SSH handshake and login attempt is made using the **phpseclib3** library within a **10-second timeout**.

- The credential is decrypted in-memory using Laravel's `Crypt::decryptString`.
- The raw credential **is never logged, returned in the result, or stored in any audit metadata**.
- The SSH session is disconnected immediately after a successful login.

---

## Status Values

| Status        | Meaning |
|---------------|---------|
| `ssh_ok`      | TCP connected **and** SSH authentication succeeded. |
| `tcp_open`    | TCP connected but SSH check was skipped (no username or no credential). |
| `tcp_failed`  | TCP connection to host:port timed out or was refused. |
| `ssh_failed`  | TCP connected but SSH authentication failed. |
| `unreachable` | Host could not be reached at all (DNS failure, network drop). |
| `error`       | Unexpected error (e.g. decryption failure, SSH library exception). |
| `online`      | Reserved for future use (e.g. ICMP ping). |
| `unknown`     | No health check has been run yet (default). |

---

## Where Results Are Displayed

### Target Servers Index (`/admin/target-servers`)
- A **Health** column shows the status badge, last-checked time (WIB), and TCP latency.
- A compact **Health Check** button per row triggers a fresh check.

### Target Server Edit Page (`/admin/target-servers/{id}/edit`)
- A **Server Health Status** panel shows: status badge, last-checked datetime, latency, and message.
- A **Run Health Check** button is embedded in the panel header.

### Access Request Review Page (`/admin/access-requests/{id}`)
- A **Server Health** field shows the current health status of the requested target server.
- A yellow warning note appears if the server is `tcp_failed`, `ssh_failed`, `unreachable`, or `error`.
- **Approval is not blocked** — the admin can still approve if appropriate.

### Admin Dashboard (`/admin`)
- A **Server Health** summary card shows the count of healthy servers and highlights unhealthy ones in red with an animated pulse.

---

## Audit Events

Every health check creates an entry in the Audit Log with one of:

| Event | Condition |
|-------|-----------|
| `target_server_health_check_succeeded` | Status is `ssh_ok`, `tcp_open`, or `online` |
| `target_server_health_check_failed`    | Any other status |

**Metadata logged (safe — no secrets):**
```
target_server_id, host, port, status, latency_ms
```

**Metadata never logged:**
```
password, private_key, encrypted credential blob, token secret
```

---

## Route

```
POST /admin/target-servers/{targetServer}/health-check
Name: admin.target-servers.health-check
Middleware: auth, verified, admin
```

---

## Manual Testing Steps

1. **Open Target Servers**: Navigate to `/admin/target-servers`.
2. **Run a health check**: Click the **Health Check** button next to a server row.
3. **Verify success**: If the server is reachable and credentials are valid, the Health column should show `SSH OK` (emerald badge).
4. **Verify TCP-only**: Remove the SSH username to confirm `tcp_open` is returned.
5. **Test SSH failure**: Use incorrect credentials or block SSH to confirm `ssh_failed`.
6. **Test unreachable host**: Use a non-existent or firewalled IP to confirm `tcp_failed` or `unreachable`.
7. **Check Audit Logs**: Navigate to `/admin/audit-logs` and confirm `target_server_health_check_succeeded` or `_failed` entries appear.
8. **Confirm no secret exposure**: Inspect audit log metadata in the database — no `password`, `key`, or encrypted blob should appear.

---

## Troubleshooting

### Host unreachable / `tcp_failed`
- Verify the host IP or hostname is correct in the target server configuration.
- Check that the server is powered on and the network is accessible from the PAM JIT host.
- For Proxmox-imported VMs, ensure the guest OS has booted and has a valid IP assigned.

### Port closed / `tcp_failed`
- The SSH daemon may not be running. Restart it: `systemctl start sshd`.
- Check that the port configured matches the actual SSH port on the target.

### SSH authentication failed / `ssh_failed`
- The stored credential may be wrong. Re-save the server with the correct password or private key.
- Confirm that the `ssh_username` exists on the target system.
- Check that the target's `authorized_keys` contains the matching public key (if using key-based auth).
- Some hardened systems disable password authentication — switch to `private_key` auth.

### Firewall issue
- The PAM JIT server's outbound IP may be blocked by the target's firewall.
- Add the PAM JIT host IP to the target's SSH `AllowUsers` or firewall allow-list.

### Stale IP after Proxmox import
- After importing a VM via Proxmox, the VM may not yet have its IP configured.
- Wait for the VM to fully boot, then update the host field in the target server record.
- Re-run the health check after the update.

### Decryption error (`error` status)
- The server's stored credential was saved with a different `APP_KEY`.
- Re-save the server credential to re-encrypt it with the current key.
