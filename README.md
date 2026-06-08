# PAM JIT

PAM JIT is a Laravel-based privileged access management MVP for just-in-time SSH access. Users request temporary access to target servers, admins approve or reject those requests, and approved requests create time-limited JIT sessions that can execute SSH commands without exposing stored credentials to users.

## Key Features

- Role-based access for admins and regular users.
- Admin-only target server CRUD with encrypted SSH credentials.
- SSH connection testing for target servers.
- Initial Proxmox integration for connection testing, QEMU VM listing, and importing VMs as target servers.
- User access request workflow.
- Admin approval and rejection workflow.
- Time-limited JIT sessions with automatic expiry monitoring.
- Web-based SSH command execution for active sessions.
- Command blocking policy for dangerous commands.
- Database notifications for request/session events.
- Centralized audit logs and command logs.
- Admin and user dashboards.

## Tech Stack

- Laravel
- Laravel Breeze authentication
- Blade and Tailwind CSS
- MySQL or another Laravel-supported database
- Laravel database notifications
- Laravel mail notifications
- Laravel Socialite for Google OAuth
- phpseclib for SSH

## Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Configure your database in `.env`, then run:

```bash
php artisan migrate --seed
npm run build
```

For local development:

```bash
php artisan serve
npm run dev
```

## Environment Configuration

Important `.env` values:

```env
APP_NAME="PAM JIT"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pam_jit
DB_USERNAME=
DB_PASSWORD=

PROXMOX_HOST=
PROXMOX_PORT=8006
PROXMOX_NODE=
PROXMOX_TOKEN_ID=
PROXMOX_TOKEN_SECRET=
PROXMOX_VERIFY_SSL=true

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=no-reply@pam-jit.local
MAIL_FROM_NAME="${APP_NAME}"
```

The application timezone is configured as `Asia/Jakarta` in `config/app.php`. If configuration is cached after changing config values, run:

```bash
php artisan config:clear
```

## Google OAuth

Email/password login remains enabled. To allow users to continue with Google, create OAuth credentials in Google Cloud Console and set these values in `.env`:

```env
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

The Google redirect URI must match the callback route exactly: `/auth/google/callback`. New OAuth users are created with the regular `user` role; they are not made admins automatically.

## Mail Notifications

Important workflow notifications are sent to the database notification center and to the configured mail channel. Configure Laravel mail settings in `.env` for your mail provider. For local testing without sending real email, use:

```env
MAIL_MAILER=log
```

With the log mailer, generated emails are written to `storage/logs/laravel.log`. You can inspect them with:

```bash
tail -f storage/logs/laravel.log
```

For SMTP delivery later, set `MAIL_MAILER=smtp` plus `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`, `MAIL_FROM_ADDRESS`, and `MAIL_FROM_NAME`. Mailtrap, Mailpit, or an SMTP relay can be used for testing. Gmail SMTP may require an app password depending on account settings. Never commit `.env` or real mail credentials.

Notification emails include requester, target server, duration, status, and a related dashboard link. They do not include SSH passwords, private keys, or Proxmox token secrets. Admins can send a safe test message from `/admin/mail-test`.

## Database Migration and Seeding

Run migrations and demo seed data:

```bash
php artisan migrate --seed
```

To reset the database during development:

```bash
php artisan migrate:fresh --seed
```

## Scheduler Setup

The scheduler runs the JIT session monitor every minute. It sends expiry warnings, expires elapsed sessions, updates related access requests, sends notifications, and writes audit logs.

See [docs/scheduler.md](docs/scheduler.md) for cron setup.

## Default Demo Accounts

| Role | Email | Password |
| --- | --- | --- |
| Admin | admin@example.com | password |
| User | user@example.com | password |

The seeder also creates one inactive placeholder target server using `127.0.0.1`. It does not contain real SSH credentials.

## Basic Workflow

1. Admin creates or configures a target server, or imports a Proxmox QEMU VM as a target server.
2. User submits an access request for an active target server.
3. Admin approves or rejects the request.
4. Approval creates an active JIT session with an expiry time.
5. User opens the active session and runs allowed SSH commands.
6. Command attempts are written to command logs.
7. Request, session, notification, and command events are written to audit logs.
8. The scheduler warns users before expiry and expires sessions automatically.

## Security Notes

- SSH passwords and private keys are encrypted with Laravel Crypt before storage.
- Decrypted credentials are never displayed in forms, tables, logs, notifications, command logs, or audit logs.
- Proxmox API token secrets are never displayed or logged.
- Users can only view and use their own access requests and JIT sessions.
- Admin-only routes protect target server management, approvals, audit logs, and command logs.
- The command policy blocks dangerous command patterns before SSH execution.
- Sessions can expire automatically or be revoked by an admin.
- This is an MVP, not a full interactive terminal or complete PAM replacement.

## More Documentation

- [Scheduler setup](docs/scheduler.md)
- [Email notifications](docs/email-notifications.md)
- [Workflow](docs/workflow.md)
- [Security](docs/security.md)
