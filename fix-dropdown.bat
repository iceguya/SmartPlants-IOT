@echo off
echo ========================================
echo   SmartPlants - Fix Dropdown Bug
echo ========================================
echo.

REM Stop npm run dev jika masih berjalan
echo [1/3] Stopping Vite dev server...
taskkill /F /IM node.exe 2>nul
timeout /t 2 >nul

REM Hapus file hot
echo [2/3] Removing public/hot file...
if exist public\hot (
    del public\hot
    echo ✓ File hot deleted
) else (
    echo ✓ File hot not found
)

REM Build production assets
echo [3/3] Building production assets...
call npm run build

echo.
echo ========================================
echo   ✓ Dropdown should work now!
echo ========================================
echo.
echo Tips:
echo - Jangan jalankan "npm run dev" saat development
echo - Gunakan "npm run build" setelah edit CSS/JS
echo - Atau edit file ini untuk auto-build
echo.
pause
