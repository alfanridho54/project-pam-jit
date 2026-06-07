# Scheduler Setup

Laravel's scheduler should run once per minute from cron. The scheduler will decide which configured tasks are due.

## Cron Entry

For this project path, add the following entry to the server crontab:

```cron
* * * * * cd /var/www/pam-jit && php artisan schedule:run >> /dev/null 2>&1
```

Edit the crontab with:

```bash
crontab -e
```

## Scheduled Task

The app schedules:

```bash
php artisan jit:sessions-monitor
```

The monitor command:

- sends one expiry warning for active sessions expiring within 5 minutes
- marks elapsed active sessions as expired
- updates related access requests to expired
- sends database notifications
- writes audit logs

## Inspect the Schedule

Use:

```bash
php artisan schedule:list
```

You should see `jit:sessions-monitor` due every minute.

## Manual Run

You can run the monitor manually:

```bash
php artisan jit:sessions-monitor
```

This is useful for development and troubleshooting.
