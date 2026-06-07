# Workflow

## Access Request

Regular users create access requests from the web app. They can only select active target servers. A request includes:

- target server
- reason
- requested duration in minutes

Users can view only their own requests.

## Proxmox VM Import

Admins can connect to Proxmox using API token credentials configured in the environment. The initial integration supports:

- testing the Proxmox API connection
- listing QEMU VMs from the configured node
- detecting VM IP addresses through the guest agent when available
- importing a VM as a target server

VM power operations, cloning, deletion, and lifecycle management are intentionally out of scope for the MVP.

## Admin Approval and Rejection

Admins can view all access requests.

For pending requests, an admin can:

- approve the request
- reject the request with a rejection reason

Approval records approval metadata and creates an active JIT session. Rejection records rejection metadata and notifies the requester.

## JIT Session Lifecycle

Approved access creates a JIT session with:

- start time
- expiry time
- active status

Sessions can become:

- active
- expired
- revoked
- closed

The monitor command expires active sessions once their expiry time has passed. Admins can revoke active sessions manually with a reason.

## Notifications

The app uses Laravel database notifications.

Notifications are sent when:

- a user creates an access request
- an admin approves a request
- an admin rejects a request
- a JIT session is revoked
- a JIT session is expiring soon
- a JIT session expires

Users and admins share the same notification page.

## Command Execution and Logging

Users can run SSH commands only through their own active and usable JIT sessions.

Before execution:

- session ownership is checked
- session usability is checked
- target server activity is checked
- command policy is checked

Every command attempt is recorded as a command log with status:

- success
- failed
- blocked
- denied

Audit logs are also written for command execution outcomes.
