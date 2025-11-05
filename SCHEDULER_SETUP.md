# Laravel Task Scheduler Setup

## Overview
The application automatically marks prenatal checkups as "missed" if the appointment date has passed. This runs at **5:00 PM and 11:59 PM daily** (Philippine Time).

## How It Works

### Scheduled Task
- **Command:** `checkups:mark-todays-missed`
- **Schedule:** Runs daily at 5:00 PM and 11:59 PM (Asia/Manila timezone)
- **What it does:**
  - Finds all prenatal checkups with status "upcoming" where the checkup_date has passed
  - Marks them as "missed"
  - Sets `auto_missed = true`
  - Records the missed_date and missed_reason
  - Creates notifications for healthcare workers

### Files Involved
1. `app/Console/Commands/MarkTodaysMissedCheckups.php` - The command that marks checkups
2. `app/Console/Kernel.php` - Schedules the command to run daily
3. `config/app.php` - Timezone set to 'Asia/Manila'

## Setup Instructions

### For Windows (XAMPP/Development)

Since you're using XAMPP on Windows, you have two options:

#### Option 1: Windows Task Scheduler (Recommended for Production)

1. Open **Task Scheduler** (search for it in Windows)
2. Click **"Create Basic Task"**
3. Name: `Laravel Scheduler - Health Care`
4. Trigger: **Daily**
5. Start time: **4:00 PM** (runs every minute from 4 PM to midnight to catch the 5 PM and 11:59 PM tasks)
6. Action: **Start a program**
7. Program/script:
   ```
   C:\xampp\php\php.exe
   ```
8. Add arguments:
   ```
   C:\xampp\htdocs\capstone\health-care\artisan schedule:run
   ```
9. Start in:
   ```
   C:\xampp\htdocs\capstone\health-care
   ```
10. In **Advanced settings**, check:
    - ✓ Run whether user is logged on or not
    - ✓ Run with highest privileges
11. In **Triggers**, edit the trigger:
    - Repeat task every: **1 minute**
    - For a duration of: **Indefinitely**

#### Option 2: Keep Command Prompt Open (For Development/Testing)

Run this command in Command Prompt and keep it open:

```bash
cd C:\xampp\htdocs\capstone\health-care
php artisan schedule:work
```

This will run the scheduler every minute while the command prompt is open.

### For Linux/Mac (Production Server)

Add this cron job:

```bash
* * * * * cd /path/to/health-care && php artisan schedule:run >> /dev/null 2>&1
```

To edit crontab:
```bash
crontab -e
```

## Manual Testing

You can manually run the command anytime to test:

```bash
cd C:\xampp\htdocs\capstone\health-care
php artisan checkups:mark-todays-missed
```

This will immediately mark any past-due checkups as missed.

## Verification

To verify the scheduler is working:

1. Check the Laravel logs:
   ```
   storage/logs/laravel.log
   ```

2. Create a test prenatal checkup with a past date and status "upcoming"

3. Run the command manually or wait for the scheduled time

4. Check if the status changed to "missed"

## Troubleshooting

### Command not running automatically
- Make sure Task Scheduler task is enabled
- Check if PHP path is correct in Task Scheduler
- Verify the task is set to repeat every 1 minute

### Wrong timezone
- Check `config/app.php` - should be `'timezone' => 'Asia/Manila'`
- Run `php artisan config:clear` after changing timezone

### No checkups being marked
- Verify there are checkups with status "upcoming" and checkup_date in the past
- Run the command manually to see output
- Check database for `auto_missed` field being set to true

## Notes

- The scheduler only marks checkups with status "upcoming"
- Checkups that are already "done" or "missed" are not affected
- The `auto_missed` field is set to `true` to distinguish from manually marked missed checkups
- Notifications are created for healthcare workers when checkups are auto-marked as missed
