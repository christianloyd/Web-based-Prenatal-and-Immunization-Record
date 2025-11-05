@echo off
title Healthcare System - Scheduler
color 0A
echo.
echo ====================================
echo   Healthcare System Scheduler
echo ====================================
echo.
echo Starting scheduler...
echo Keep this window open while developing
echo.
cd C:\xampp\htdocs\capstone\health-care
php artisan schedule:work
pause
