# Security

## Credential Encryption

Target server SSH passwords and private keys are encrypted with Laravel Crypt before storage.

The application decrypts credentials only inside SSH services when testing a connection or executing a command. Decrypted credentials are never displayed or logged.

## Admin-Only Target Server Management

Only admins can:

- create target servers
- import Proxmox VMs as target servers
- edit target servers
- delete target servers
- test SSH credentials
- approve or reject access requests
- revoke sessions
- view audit logs
- view command logs

## User-Scoped Sessions

Regular users can only access their own:

- access requests
- JIT sessions
- command execution pages
- notifications

Users cannot execute commands on another user's session.

## Command Blocking Policy

The command policy blocks dangerous command patterns before SSH execution, including destructive filesystem, user-management, reboot, and disk-formatting commands.

This policy is intentionally conservative, but it is not a complete shell sandbox.

## Audit Logging

The app records centralized audit logs for important actions, including:

- target server changes
- access request lifecycle events
- JIT session lifecycle events
- notification read actions
- SSH command outcomes

Audit metadata avoids decrypted credentials.

## Proxmox Token Handling

Proxmox integration uses API token authentication from environment variables. The token secret is read from configuration for API calls only. It is never displayed in the UI and should not be written to audit logs or application logs.

## MVP Limitations

- This is not a full interactive terminal.
- Command policy uses pattern matching and is not a complete command parser.
- There is no per-command approval workflow yet.
- There is no session recording or keystroke replay.
- There is no network-level isolation or bastion host integration.
- SSH key passphrases are not currently modeled separately.
- Proxmox integration currently supports connection testing, QEMU VM listing, and importing VMs as target servers only.
- Production deployments should add stronger operational controls, monitoring, and infrastructure hardening.
