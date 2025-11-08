# Quick Commands Cheat Sheet - Healthcare System

## 📌 KEEP THIS OPEN WHILE DEVELOPING

---

## ⚡ MOST IMPORTANT - Start Scheduler

```bash
cd C:\xampp\htdocs\capstone\health-care
php artisan schedule:work
```
**👉 Keep this window open and minimized!**

---

## 🔥 Most Used Commands

| Command | What It Does |
|---------|-------------|
| `php artisan optimize:clear` | Clear all caches |
| `php artisan view:clear` | Clear view cache (after editing Blade files) |
| `php artisan config:clear` | Clear config cache (after editing .env) |
| `php artisan migrate` | Run database migrations |
| `php artisan checkups:mark-todays-missed` | Mark missed checkups NOW |

## 🚀 One-Click Batch Files (Double-click to run)

| File | What It Does | When to Use |
|------|-------------|-------------|
| `start-scheduler.bat` | Start scheduler | Every day when you start coding |
| `clear-cache.bat` | Clear all caches | After editing Blade/config files |
| `mark-missed-now.bat` | Mark missed checkups | Testing missed checkups |
| `optimize-performance.bat` | Optimize for production | Before deploying |
| `disable-optimization.bat` | Back to development mode | After using optimize-performance.bat |

---

## 📂 Project Navigation

```bash
# Open Command Prompt and type:
cd C:\xampp\htdocs\capstone\health-care
```

---

## 🗄️ Database Commands

```bash
# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Reset database (⚠️ deletes all data!)
php artisan migrate:fresh
```

---

## 🧹 Cache Clearing

```bash
# Clear everything (use this most)
php artisan optimize:clear

# After editing Blade views
php artisan view:clear

# After editing .env file
php artisan config:clear
```

---

## 🐛 Debugging

```bash
# View logs
type storage\logs\laravel.log

# Interactive PHP (Tinker)
php artisan tinker
>>> App\Models\PrenatalCheckup::count()
>>> exit

# List all commands
php artisan list
```

---

## ⏰ Scheduler Commands

```bash
# Start scheduler (keep running)
php artisan schedule:work

# Run scheduler once
php artisan schedule:run

# Mark missed checkups
php artisan checkups:mark-todays-missed

# View scheduled tasks
php artisan schedule:list
```

---

## 🚨 When Something Breaks

**Try these in order:**

1. Clear caches
   ```bash
   php artisan optimize:clear
   ```

2. Regenerate autoload
   ```bash
   composer dump-autoload
   ```

3. Check logs
   ```bash
   type storage\logs\laravel.log
   ```

---

## ✅ Daily Checklist

- [ ] Start XAMPP (Apache + MySQL)
- [ ] Open Command Prompt
- [ ] Navigate to project: `cd C:\xampp\htdocs\capstone\health-care`
- [ ] Start scheduler: `php artisan schedule:work` (keep open)
- [ ] Start coding!

---

## 💡 Pro Tips

- Press `↑` to see previous commands
- Press `Ctrl + C` to stop a running command
- Press `Ctrl + L` to clear the screen
- Always run commands from project folder
- Keep scheduler running during development

---

## 🚀 Production (Laravel Forge)

1. Push code to GitHub/GitLab
2. Deploy in Forge dashboard
3. Enable "Scheduler" toggle in Forge
4. Done! ✅

---

## 📞 Need Help?

See full reference: `TERMINAL_COMMANDS_REFERENCE.md`

---

**Print this page and keep it near your workspace!**
