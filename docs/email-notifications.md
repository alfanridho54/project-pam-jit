# Email Notifications

PAM JIT sends important workflow notifications through Laravel database notifications and the configured Laravel mail channel.

Email content must stay operational and safe. Do not include SSH passwords, private keys, Proxmox token secrets, or other sensitive credentials in mail bodies, subjects, logs, or audit metadata.

## Local Testing With Logs

For local development, keep mail in log mode:

```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS=no-reply@pam-jit.local
MAIL_FROM_NAME="${APP_NAME}"
```

Trigger a workflow notification or use the admin-only Mail Test page at:

```text
/admin/mail-test
```

Then inspect the Laravel log:

```bash
tail -f storage/logs/laravel.log
```

With `MAIL_MAILER=log`, Laravel writes the rendered email to `storage/logs/laravel.log` instead of sending it to a real recipient.

## SMTP Configuration

When you are ready to send real email, update `.env` with your SMTP provider settings:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

Depending on your provider, `MAIL_PORT` and `MAIL_ENCRYPTION` may differ. Common combinations are:

- Port `587` with `tls`
- Port `465` with `ssl`
- Port `1025` or `2525` with `null` for local testing tools

After changing mail configuration in a cached environment, run:

```bash
php artisan config:clear
```

## Testing Providers

You can test SMTP delivery with tools such as:

- Mailtrap
- Mailpit
- Any internal or hosted SMTP relay

Gmail SMTP can require an app password depending on account settings, especially when two-factor authentication is enabled. Do not use a personal account password as an application secret.

## Secret Handling

Never commit `.env`. Keep real SMTP usernames, passwords, OAuth secrets, and Proxmox tokens only in local or deployment secret storage.

Safe placeholders belong in `.env.example`; real credentials do not.
