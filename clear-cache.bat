@echo off
title Healthcare System - Clear Cache
color 0E
echo.
echo ====================================
echo   Clearing All Caches
echo ====================================
echo.
cd C:\xampp\htdocs\capstone\health-care
php artisan optimize:clear
echo.
echo ====================================
echo   Cache Cleared Successfully!
echo ====================================
echo.
pause
