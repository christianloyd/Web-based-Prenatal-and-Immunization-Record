# Terminal Commands Reference - Healthcare System

This file contains all the important terminal commands you'll need for development and maintenance.

---

## Table of Contents
1. [Daily Development Commands](#daily-development-commands)
2. [Scheduler Commands](#scheduler-commands)
3. [Database Commands](#database-commands)
4. [Cache Clearing Commands](#cache-clearing-commands)
5. [Testing Commands](#testing-commands)
6. [Deployment Commands](#deployment-commands)
7. [Troubleshooting Commands](#troubleshooting-commands)

---

## Daily Development Commands

### Start Your Development Environment

**1. Start XAMPP (Apache + MySQL)**
- Open XAMPP Control Panel
- Click "Start" for Apache
- Click "Start" for MySQL

**2. Navigate to Project**
```bash
cd C:\xampp\htdocs\capstone\health-care
```

**3. Start the Scheduler (KEEP THIS RUNNING)**
```bash
php artisan schedule:work
```
📝 **Important:** Keep this window open and minimized while developing. This runs the automatic missed checkup marking.

---

## Scheduler Commands

### Run the Scheduler (Development)
```bash
# Option 1: Keep running (Recommended)
php artisan schedule:work

# Option 2: Run once
php artisan schedule:run
```

### Manually Mark Missed Checkups
```bash
# Run this anytime to immediately mark past checkups as missed
php artisan checkups:mark-todays-missed
```

### View All Scheduled Tasks
```bash
php artisan schedule:list
```

---

## Database Commands

### Run Migrations
```bash
# Run all pending migrations
php artisan migrate

# Run migrations with force (production)
php artisan migrate --force

# Rollback last migration
php artisan migrate:rollback

# Rollback all migrations and re-run them
php artisan migrate:fresh

# Rollback and re-run with seed data
php artisan migrate:fresh --seed
```

### Create New Migration
```bash
# Create a new migration file
php artisan make:migration create_table_name

# Example:
php artisan make:migration add_field_to_users_table
```

### Database Seeding
```bash
# Run all seeders
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=UserSeeder
```

### Check Database Connection
```bash
php artisan tinker
>>> DB::connection()->getPdo();
>>> exit
```

---

## Cache Clearing Commands

### Clear All Caches (Most Common)
```bash
# Clear everything at once
php artisan optimize:clear
```

This clears:
- Application cache
- Route cache
- Config cache
- View cache
- Compiled files

### Clear Individual Caches
```bash
# Clear view cache (after editing Blade files)
php artisan view:clear

# Clear config cache (after editing .env or config files)
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear application cache
php artisan cache:clear

# Clear compiled classes
php artisan clear-compiled
```

### When to Clear Cache
- ✅ After editing `.env` file → `php artisan config:clear`
- ✅ After editing Blade views → `php artisan view:clear`
- ✅ After updating routes → `php artisan route:clear`
- ✅ When in doubt → `php artisan optimize:clear`

---

## Testing Commands

### Check if Command Exists
```bash
php artisan list
```

### Test Specific Command
```bash
# Test mark missed checkups
php artisan checkups:mark-todays-missed

# Test notification sending
php artisan notifications:check
```

### Check Artisan Version
```bash
php artisan --version
```

### Interactive PHP Shell (Tinker)
```bash
php artisan tinker
```

Example queries in Tinker:
```php
// Get first patient
App\Models\Patient::first()

// Count upcoming checkups
App\Models\PrenatalCheckup::where('status', 'upcoming')->count()

// Get all missed checkups
App\Models\PrenatalCheckup::where('status', 'missed')->get()

// Exit Tinker
exit
```

---

## Deployment Commands

### Production Deployment Checklist

**1. Upload project to server**

**2. Set up .env file**
```bash
# Copy example
cp .env.example .env

# Edit with production values
nano .env
```

**3. Install dependencies**
```bash
composer install --no-dev --optimize-autoloader
```

**4. Generate app key**
```bash
php artisan key:generate
```

**5. Run migrations**
```bash
php artisan migrate --force
```

**6. Set up storage link**
```bash
php artisan storage:link
```

**7. Optimize for production**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**8. Set up cron job (Linux)**
```bash
crontab -e
```
Add this line:
```
* * * * * cd /path/to/health-care && php artisan schedule:run >> /dev/null 2>&1
```

---

## Troubleshooting Commands

### View Laravel Logs
```bash
# Windows
type storage\logs\laravel.log

# View last 50 lines
Get-Content storage\logs\laravel.log -Tail 50

# Linux/Mac
tail -f storage/logs/laravel.log
```

### Check PHP Version
```bash
php -v
```

### Check Composer Version
```bash
composer -V
```

### Check MySQL Connection
```bash
# Test connection through artisan
php artisan tinker
>>> DB::connection()->getPdo();
```

### Fix Permission Issues (Linux/Production)
```bash
# Set correct permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Set owner to web server user
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
```

### Regenerate Autoload Files
```bash
composer dump-autoload
```

### Queue Commands (If using queues)
```bash
# Start queue worker
php artisan queue:work

# Restart queue workers
php artisan queue:restart

# Clear failed jobs
php artisan queue:flush
```

---

## Quick Reference - Most Used Commands

```bash
# Navigate to project
cd C:\xampp\htdocs\capstone\health-care

# Start scheduler (keep running)
php artisan schedule:work

# Mark missed checkups NOW
php artisan checkups:mark-todays-missed

# Clear all caches
php artisan optimize:clear

# Clear view cache (after editing Blade files)
php artisan view:clear

# Run migrations
php artisan migrate

# Check Laravel version
php artisan --version

# View logs
type storage\logs\laravel.log
```

---

## Laravel Forge Specific Commands

### After Connecting to Server via SSH

```bash
# Navigate to your site
cd yourdomain.com

# Run migrations
php artisan migrate --force

# Clear cache
php artisan optimize:clear

# View logs
tail -f storage/logs/laravel.log

# Restart queue workers (if using)
php artisan queue:restart
```

### Enable Scheduler in Laravel Forge
- Go to your site in Forge dashboard
- Scroll to "Scheduler" section
- Toggle switch to **ON**
- Done! ✅

---

## Development Workflow

### Typical Daily Workflow:

**Morning:**
```bash
# 1. Open Command Prompt
cd C:\xampp\htdocs\capstone\health-care

# 2. Start scheduler (keep running in background)
php artisan schedule:work

# 3. Start coding!
```

**After Making Changes:**
```bash
# If you edited Blade views
php artisan view:clear

# If you edited .env or config files
php artisan config:clear

# If you edited routes
php artisan route:clear

# If you created new migration
php artisan migrate
```

**Before Committing Code:**
```bash
# Clear all caches
php artisan optimize:clear

# Test that everything works
# Refresh browser and test functionality
```

---

## Emergency Commands

### Something Not Working?

**Try these in order:**

```bash
# 1. Clear all caches
php artisan optimize:clear

# 2. Regenerate autoload
composer dump-autoload

# 3. Restart scheduler
# Stop schedule:work (Ctrl+C)
# Start again
php artisan schedule:work

# 4. Check logs for errors
type storage\logs\laravel.log
```

### Reset Everything (Last Resort)

```bash
# ⚠️ WARNING: This will delete all data!
# Only use in development, never in production!

php artisan migrate:fresh --seed
```

---

## Keyboard Shortcuts

- `Ctrl + C` - Stop running command
- `Ctrl + L` - Clear terminal screen
- `↑` - Previous command
- `↓` - Next command
- `Tab` - Auto-complete file/folder names

---

## Notes

- **Always run commands from the project directory**: `C:\xampp\htdocs\capstone\health-care`
- **Keep scheduler running during development**: `php artisan schedule:work`
- **Clear caches after making changes**: `php artisan optimize:clear`
- **Check logs when debugging**: `storage\logs\laravel.log`

---

## Help Commands

```bash
# Get help for any command
php artisan help <command>

# Example:
php artisan help migrate

# List all available commands
php artisan list

# List all routes
php artisan route:list
```

---

## Useful Aliases (Optional)

You can create shortcuts for common commands. Create a `.bat` file:

**clear-cache.bat:**
```batch
@echo off
cd C:\xampp\htdocs\capstone\health-care
php artisan optimize:clear
echo Cache cleared successfully!
pause
```

**start-scheduler.bat:**
```batch
@echo off
cd C:\xampp\htdocs\capstone\health-care
php artisan schedule:work
```

Save these in your project root and double-click to run them!

---

## Additional Resources

## Laravel Documentation: https://laravel.com/docs
## Laravel Forge Documentation: https://forge.laravel.com/docs
## Artisan Console: https://laravel.com/docs/artisan

---

**Last Updated:** November 1, 2025
**Project:** Healthcare System
**Environment:** XAMPP (Development) / Laravel Forge (Production)

## php artisan tinker --execute="echo 'Pending jobs: ' . DB: :table('jobs')->count(); echo PHP_EOL'"
## php artisan queue:work
## php artisan queue:work --stop-when-empty