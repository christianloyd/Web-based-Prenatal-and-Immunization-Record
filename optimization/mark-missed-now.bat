@echo off
title Healthcare System - Mark Missed Checkups
color 0C
echo.
echo ====================================
echo   Marking Missed Checkups
echo ====================================
echo.
cd C:\xampp\htdocs\capstone\health-care
php artisan checkups:mark-todays-missed
echo.
echo ====================================
echo   Done!
echo ====================================
echo.
pause
