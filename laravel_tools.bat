@echo off
title Laravel Tools - ES Group System
color 0A

echo ============================================
echo       LARAVEL MAINTENANCE TOOL (AUTO)
echo ============================================
echo.
echo 1. Clear All Caches
echo 2. Run All Optimize Commands
echo 3. Fresh Migrate + Seed
echo 4. Dump Autoload
echo 5. Run Everything
echo 0. Exit
echo.

set /p choice="Choose an option: "

if "%choice%"=="1" goto clear
if "%choice%"=="2" goto optimize
if "%choice%"=="3" goto fresh
if "%choice%"=="4" goto autoload
if "%choice%"=="5" goto all
if "%choice%"=="0" exit

:clear
echo Clearing Laravel caches...
php artisan optimize:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo Done!
pause
exit

:optimize
echo Running optimize...
php artisan optimize
composer dump-autoload
echo Done!
pause
exit

:fresh
echo This will delete ALL tables and re-run migrations!
pause
php artisan migrate:fresh --seed
pause
exit

:autoload
echo Dumping autoload...
composer dump-autoload
pause
exit

:all
echo Running ALL operations...
php artisan optimize:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
composer dump-autoload
php artisan optimize
echo DONE!
pause
exit
