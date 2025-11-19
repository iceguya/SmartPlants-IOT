@echo off
echo ================================================
echo  Fix Provisioning Tokens - Add user_id Column
echo ================================================
echo.
echo This script will add the missing user_id column
echo to the provisioning_tokens table.
echo.
pause

echo Running migration...
php artisan migrate --path=database/migrations/2025_11_16_000001_add_user_id_to_provisioning_tokens_table.php

echo.
echo ================================================
echo  Migration Complete!
echo ================================================
echo.
echo The user_id column has been added to the
echo provisioning_tokens table.
echo.
pause
