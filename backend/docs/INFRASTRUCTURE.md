# Backend Infrastructure

## Queue worker

Background jobs (emails, notifications, async payment confirmation) use Laravel queues.

- **Recommended:** Redis. Set in `.env`:
  - `QUEUE_CONNECTION=redis`
  - `REDIS_HOST=127.0.0.1`, `REDIS_PASSWORD=null`, `REDIS_PORT=6379`
- **Alternative:** Database driver: `QUEUE_CONNECTION=database` and run `php artisan queue:table` + migrate.

**Run the worker (development):**

```bash
php artisan queue:work
```

**Production (Supervisor example):**

```ini
[program:hotel-booking-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/backend/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
numprocs=1
user=www-data
redirect_stderr=true
stdout_logfile=/path/to/backend/storage/logs/worker.log
stopwaitsecs=3600
```

Replace `/path/to/backend` with the actual project path.

---

## Scheduler

The Laravel scheduler runs scheduled tasks (daily reports, cleanup, availability batch jobs).

**Single cron entry (run every minute):**

```bash
* * * * * cd /path/to/backend && php artisan schedule:run >> /dev/null 2>&1
```

Replace `/path/to/backend` with the actual project path.

Define scheduled jobs in `routes/console.php` using `Schedule::...`.
