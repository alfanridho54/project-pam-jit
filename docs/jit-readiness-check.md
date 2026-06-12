# JIT Readiness Check

## Overview

The **JIT Readiness Check** verifies whether a target server is properly configured
to support temporary Linux credential provisioning required by the PAM JIT workflow.

It complements the existing **Health Check**, which only confirms TCP reachability
and SSH authentication.

---

## Health Check vs. JIT Readiness Check

| Aspect                 | Health Check                       | JIT Readiness Check                         |
|------------------------|------------------------------------|---------------------------------------------|
| **Purpose**            | Is the server reachable via SSH?   | Can the server create temporary users?      |
| **What it tests**      | TCP connect + SSH login            | sudo NOPASSWD for user management commands  |
| **Passes when**        | SSH auth succeeds                  | All 5 sudo commands succeed without password |
| **Fails when**         | TCP refused or SSH auth denied     | One or more sudo commands require a password |
| **Creates a user?**    | No                                 | No                                          |
| **Modifies server?**   | No                                 | No                                          |

A server can pass the Health Check (SSH login works) but still fail the JIT
Readiness Check if the SSH user lacks passwordless sudo privileges for user
management commands.

---

## Required sudo NOPASSWD Commands

The SSH user configured on the target server must be able to run the following
commands without being prompted for a password:

```
sudo -n /usr/bin/id
sudo -n /usr/sbin/useradd
sudo -n /usr/sbin/chpasswd
sudo -n /usr/sbin/usermod
sudo -n /usr/sbin/userdel
```

Additionally, `/usr/bin/pkill` is used during session revocation to terminate
active SSH sessions belonging to temporary users.

---

## Recommended sudoers Configuration

For a lab or testing environment, add the following to `/etc/sudoers.d/pam-jit`
on each target server:

```sudoers
# PAM JIT temporary credential management
# Allow the SSH user to manage users and kill sessions without a password
student01 ALL=(ALL) NOPASSWD: /usr/bin/id, /usr/sbin/useradd, /usr/sbin/usermod, /usr/sbin/userdel, /usr/sbin/chpasswd, /usr/bin/pkill
```

Replace `student01` with the actual SSH username configured in PAM JIT.

### Applying the Configuration

```bash
# Create the sudoers drop-in file
sudo tee /etc/sudoers.d/pam-jit << 'EOF'
student01 ALL=(ALL) NOPASSWD: /usr/bin/id, /usr/sbin/useradd, /usr/sbin/usermod, /usr/sbin/userdel, /usr/sbin/chpasswd, /usr/bin/pkill
EOF

# Set correct permissions (must be 0440)
sudo chmod 0440 /etc/sudoers.d/pam-jit

# Validate syntax
sudo visudo -cf /etc/sudoers.d/pam-jit
```

---

## Troubleshooting

### "sudo: a password is required"

**Cause**: The SSH user is not configured with NOPASSWD for the required commands.

**Fix**: Add the sudoers rule as shown above, or verify the existing rule covers
all required command paths.

### "user is not allowed to run sudo"

**Cause**: The SSH user is not in the sudoers file at all, or is explicitly
denied by a sudoers rule.

**Fix**: Ensure the user is listed in `/etc/sudoers` or a file under
`/etc/sudoers.d/` with appropriate permissions.

### "command path mismatch"

**Cause**: Commands are installed at different paths (e.g., `/sbin/useradd`
instead of `/usr/sbin/useradd`).

**Fix**: Check the actual command paths on the target server:

```bash
which useradd chpasswd usermod userdel id pkill
```

Update the sudoers rule to match the actual paths.

### "IP changed to a different VM"

**Cause**: The target server's IP now points to a different VM that does not
have the sudoers configuration.

**Fix**: Either update the target server IP in PAM JIT to the correct VM, or
apply the sudoers configuration to the new VM.

### "SSH OK but temporary credential creation fails"

**Cause**: The Health Check passes (SSH login works) but the JIT Readiness
Check was never run, or it shows `not_ready`.

**Fix**: Run the JIT Readiness Check from the Target Servers admin page. Review
the command checklist to identify which specific commands lack sudo NOPASSWD
access, then apply the appropriate sudoers configuration.
