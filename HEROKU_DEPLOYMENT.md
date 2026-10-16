# Heroku Deployment Guide

This guide will help you deploy this Laravel application to Heroku.

## Prerequisites

1. Heroku CLI installed ([Download here](https://devcenter.heroku.com/articles/heroku-cli))
2. Git installed
3. Heroku account

## Step 1: Login to Heroku

```bash
heroku login
```

## Step 2: Create Heroku App

```bash
heroku create your-app-name
```

Or if you already have an app:
```bash
heroku git:remote -a your-app-name
```

## Step 3: Set Environment Variables

Set the database connection using your MySQL connection string (get the
real values from Railway's dashboard or `heroku config:get DB_URL` --
never commit them):

```bash
heroku config:set DB_URL="mysql://<user>:<password>@<host>:<port>/<database>"
heroku config:set DB_CONNECTION=mysql
```

Or set individual database variables:

```bash
heroku config:set DB_CONNECTION=mysql
heroku config:set DB_HOST=<host>
heroku config:set DB_PORT=<port>
heroku config:set DB_DATABASE=<database>
heroku config:set DB_USERNAME=<user>
heroku config:set DB_PASSWORD=<password>
```

## Step 4: Set Other Required Environment Variables

Set your application key and other required variables:

```bash
heroku config:set APP_KEY=$(php artisan key:generate --show)
heroku config:set APP_ENV=production
heroku config:set APP_DEBUG=false
heroku config:set APP_URL=https://your-app-name.herokuapp.com
```

## Step 5: Add Buildpacks

Heroku needs to know this is a PHP application:

```bash
heroku buildpacks:set heroku/php
```

## Step 6: Configure Process Types

The Procfile is already configured with:
- `web`: Main web server
- `worker`: Queue worker for background jobs
- `scheduler`: Laravel task scheduler

## Step 7: Deploy

```bash
git add .
git commit -m "Prepare for Heroku deployment"
git push heroku main
```

Or if you're using master branch:
```bash
git push heroku master
```

## Step 8: Run Migrations

After deployment, run database migrations:

```bash
heroku run php artisan migrate --force
```

## Step 9: Scale Dynos

Scale your dynos (processes):

```bash
# Scale web dyno (required)
heroku ps:scale web=1

# Scale worker dyno (if you use queues)
heroku ps:scale worker=1

# Scale scheduler dyno (for scheduled tasks)
# Note: The scheduler uses `schedule:work` which runs continuously
# Alternatively, you can use Heroku Scheduler addon and remove this dyno
heroku ps:scale scheduler=1
```

### Alternative: Using Heroku Scheduler Addon

Instead of a dedicated scheduler dyno, you can use Heroku Scheduler addon:

1. Install the addon:
```bash
heroku addons:create scheduler:standard
```

2. Open the scheduler dashboard:
```bash
heroku addons:open scheduler
```

3. Add a job to run every 10 minutes:
   - Command: `php artisan schedule:run`
   - Frequency: Every 10 minutes

4. Update Procfile to remove scheduler line (or comment it out):
```
web: heroku-php-apache2 public/
worker: php artisan queue:work --timeout=1800 --sleep=10 --tries=3
# scheduler: php artisan schedule:work  # Using Heroku Scheduler addon instead
```

## Step 10: View Logs

Monitor your application:

```bash
heroku logs --tail
```

## Additional Configuration

### Storage

Laravel's storage needs to be writable. You may need to configure storage:

```bash
heroku run php artisan storage:link
```

### Queue Configuration

If you're using queues, make sure your queue connection is set:

```bash
heroku config:set QUEUE_CONNECTION=database
```

Or if using Redis:
```bash
heroku addons:create heroku-redis
```

### Cache Configuration

For production, use Redis for caching:

```bash
heroku config:set CACHE_DRIVER=redis
```

### Session Configuration

For production sessions:

```bash
heroku config:set SESSION_DRIVER=redis
```

## Troubleshooting

### Check Application Status

```bash
heroku ps
```

### View Specific Logs

```bash
heroku logs --tail --ps web
heroku logs --tail --ps worker
heroku logs --tail --ps scheduler
```

### Run Artisan Commands

```bash
heroku run php artisan [command]
```

### Clear Cache

```bash
heroku run php artisan cache:clear
heroku run php artisan config:clear
heroku run php artisan route:clear
heroku run php artisan view:clear
```

## Important Notes

1. **Database**: The MySQL database connection is already configured. Make sure the database is accessible from Heroku's IP ranges.

2. **Scheduler**: The scheduler process runs continuously. Alternatively, you can use Heroku Scheduler addon to run `php artisan schedule:run` every minute instead.

3. **Storage**: Heroku has an ephemeral filesystem. Files uploaded to storage will be lost on dyno restart. Consider using S3 or similar for file storage.

4. **Environment Variables**: Never commit sensitive information. Use `heroku config:set` for all secrets.

5. **SSL**: Railway databases may require SSL. If you encounter SSL errors, you may need to configure SSL options in the database configuration.

## Database SSL Configuration (if needed)

If your Railway database requires SSL, you may need to add:

```bash
heroku config:set MYSQL_ATTR_SSL_CA=/path/to/ca-cert.pem
```

Or disable SSL verification (not recommended for production):

Update `config/database.php` MySQL options to handle SSL appropriately.

