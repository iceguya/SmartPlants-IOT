@echo off
REM SmartPlants IoT - Quick Development Setup (Windows)
REM This script sets up the entire development environment

echo ====================================================================
echo    SmartPlants IoT - Development Setup (Windows)
echo ====================================================================
echo.

REM Check if .env exists
if not exist .env (
    echo [1/7] Creating .env file...
    copy .env.example .env
    echo Done: .env created
) else (
    echo [1/7] .env already exists
)

REM Generate app key
echo.
echo [2/7] Generating application key...
php artisan key:generate
echo Done: App key generated

REM Install PHP dependencies
echo.
echo [3/7] Installing Composer dependencies...
composer install
echo Done: Composer dependencies installed

REM Install Node dependencies
echo.
echo [4/7] Installing NPM dependencies...
call npm install
echo Done: NPM dependencies installed

REM Run migrations
echo.
echo [5/7] Running database migrations...
php artisan migrate
echo Done: Migrations completed

REM Clear caches
echo.
echo [6/7] Clearing caches...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo Done: Caches cleared

REM Build assets
echo.
echo [7/7] Building frontend assets...
call npm run build
echo Done: Assets built

echo.
echo ====================================================================
echo    SETUP COMPLETE! Success!
echo ====================================================================
echo.
echo Next steps:
echo 1. Configure your database in .env
echo 2. Run: php artisan migrate (if database config changed)
echo 3. Run: php artisan serve
echo 4. Visit: http://localhost:8000
echo.
echo For production deployment, see: SETUP.md
echo.
pause
