# 🚀 Quick Start - Batch Files Guide

## 📁 All Your Batch Files at a Glance

| File Name | Icon | When to Use | Frequency |
|-----------|------|-------------|-----------|
| `start-scheduler.bat` | ⏰ | Start of every work session | Daily |
| `clear-cache.bat` | 🧹 | After editing Blade/config files | As needed |
| `mark-missed-now.bat` | ✅ | Testing missed checkups | Testing only |
| `optimize-performance.bat` | 🚀 | Before deploying to production | Before deployment |
| `disable-optimization.bat` | 🔧 | Return to development mode | After testing optimization |

---

## 🎯 Your Daily Workflow

### **Every Morning:**

1. ✅ Open XAMPP → Start Apache + MySQL
2. ✅ Double-click **`start-scheduler.bat`** (keep window open)
3. ✅ Start coding!

### **While Coding:**

**After editing Blade files (`.blade.php`):**
- Double-click **`clear-cache.bat`**

**After editing `.env` or config files:**
- Double-click **`clear-cache.bat`**

### **Before Deploying:**

1. ✅ Double-click **`optimize-performance.bat`**
2. ✅ Test your app
3. ✅ If everything works → Deploy!
4. ✅ After deploying → Double-click **`disable-optimization.bat`**

---

## 📖 Detailed Descriptions

### ⏰ **start-scheduler.bat**
```
Purpose: Starts the Laravel task scheduler
Runs: php artisan schedule:work
Keep Open: YES - Minimize, don't close!
```

**What it does:**
- Automatically marks missed prenatal checkups at 5 PM
- Runs scheduled notifications
- Handles all automated tasks

**Must keep running while developing!**

---

### 🧹 **clear-cache.bat**
```
Purpose: Clears all Laravel caches
Runs: php artisan optimize:clear
Keep Open: NO - Closes automatically
```

**What it does:**
- Clears view cache (Blade templates)
- Clears config cache (.env files)
- Clears route cache
- Clears application cache

**Use after making changes to see them immediately!**

---

### ✅ **mark-missed-now.bat**
```
Purpose: Manually mark missed checkups
Runs: php artisan checkups:mark-todays-missed
Keep Open: NO - Closes automatically
```

**What it does:**
- Finds all prenatal checkups with past dates
- Changes status from "upcoming" to "missed"
- Useful for testing

**Only use for testing, normally runs automatically at 5 PM!**

---

### 🚀 **optimize-performance.bat**
```
Purpose: Optimize Laravel for production
Runs: Multiple optimization commands
Keep Open: NO - Closes automatically
```

**What it does:**
- Caches routes (70-80% faster)
- Caches config files
- Caches views
- Optimizes autoloader
- Makes app 40-50% faster overall

**⚠️ Warning:** After running this, route/config changes won't apply automatically!

**Use only before deploying to production or when testing performance!**

---

### 🔧 **disable-optimization.bat**
```
Purpose: Return to development mode
Runs: php artisan optimize:clear
Keep Open: NO - Closes automatically
```

**What it does:**
- Removes all optimizations
- Returns to normal development mode
- Changes apply immediately again

**Use after testing optimization locally!**

---

## ❓ FAQ

### **Q: Which file should I run every day?**
A: Only **`start-scheduler.bat`** - keep it running all day!

### **Q: I changed a Blade file but don't see changes**
A: Run **`clear-cache.bat`** then refresh browser

### **Q: I changed .env file but changes not working**
A: Run **`clear-cache.bat`** then restart server

### **Q: My app is slow**
A: For production, run **`optimize-performance.bat`**
   For development, it's normal (prioritizes flexibility)

### **Q: I ran optimize-performance.bat, now my changes don't show**
A: Run **`disable-optimization.bat`** to return to development mode

### **Q: Which files do I need for production server?**
A: Use **`optimize-performance.sh`** (the .sh file, not .bat)
   The .bat files are for Windows only

---

## 🎓 Pro Tips

1. **Create Desktop Shortcuts:**
   - Right-click `start-scheduler.bat`
   - Send to → Desktop (create shortcut)
   - Rename to "Start Healthcare Scheduler"
   - Now you can start from desktop!

2. **Pin to Taskbar:**
   - Drag `start-scheduler.bat` to taskbar
   - Quick access every day!

3. **Create a "Daily Startup" folder:**
   - Create folder: `C:\HealthcareStartup\`
   - Put shortcut to `start-scheduler.bat`
   - Add this folder to Windows Startup
   - Scheduler starts automatically when Windows boots!

---

## 🚨 Troubleshooting

### **Batch file opens and closes immediately**

**Cause:** Error in the script or PHP not found

**Solution:**
1. Right-click the .bat file
2. Click "Edit"
3. Add `pause` at the end
4. Save and run again
5. You'll see the error message

---

### **"php is not recognized" error**

**Cause:** PHP not in Windows PATH

**Solution:**
```bash
# Open each .bat file and change:
php artisan ...

# To:
C:\xampp\php\php.exe artisan ...
```

---

### **Scheduler not marking checkups**

**Checks:**
1. Is `start-scheduler.bat` still running?
2. Check time - it runs at 5:00 PM and 11:59 PM
3. Are there any upcoming checkups with past dates?
4. Check logs: `storage\logs\laravel.log`

---

## 📚 Related Documentation

- **Full Command Reference:** `TERMINAL_COMMANDS_REFERENCE.md`
- **Quick Cheat Sheet:** `QUICK_COMMANDS_CHEATSHEET.md`
- **Performance Guide:** `PERFORMANCE_OPTIMIZATION_GUIDE.md`
- **Scheduler Setup:** `SCHEDULER_SETUP.md`

---

## ✨ Quick Reference Card

```
┌─────────────────────────────────────────┐
│  Healthcare System - Batch Files        │
├─────────────────────────────────────────┤
│  Daily Use:                             │
│  • start-scheduler.bat (keep open)      │
│                                         │
│  As Needed:                             │
│  • clear-cache.bat (after changes)      │
│                                         │
│  Before Deployment:                     │
│  • optimize-performance.bat             │
│                                         │
│  After Testing Optimization:            │
│  • disable-optimization.bat             │
└─────────────────────────────────────────┘
```

---

**Print this page and keep it at your desk!**
