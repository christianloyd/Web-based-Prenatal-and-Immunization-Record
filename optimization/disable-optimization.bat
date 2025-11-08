@echo off
title Healthcare System - Disable Optimization (Development Mode)
color 0E

echo.
echo ============================================================
echo    Healthcare System - Disable Optimization
echo    (Return to Development Mode)
echo ============================================================
echo.

echo Clearing all cached optimizations...
echo.

php artisan optimize:clear

echo.
echo ============================================================
echo    Optimization Disabled!
echo ============================================================
echo.
echo You are now in DEVELOPMENT MODE:
echo   [32m√[0m Route changes will apply immediately
echo   [32m√[0m Config changes will apply immediately
echo   [32m√[0m View changes will apply immediately
echo.
echo [33mNote:[0m Run optimize-performance.bat again before deploying to production
echo.
echo ============================================================
echo.

pause
