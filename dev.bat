@echo off
echo ========================================
echo   SmartPlants - Development Mode
echo ========================================
echo.
echo PERHATIAN:
echo Dropdown akan bug saat Vite dev server berjalan!
echo.
echo Pilihan:
echo 1. Build sekali (recommended untuk testing)
echo 2. Watch mode dengan auto-rebuild
echo 3. Dev server dengan HMR (dropdown akan bug!)
echo 4. Exit
echo.
choice /C 1234 /N /M "Pilih mode (1-4): "

if errorlevel 4 goto end
if errorlevel 3 goto devserver
if errorlevel 2 goto watch
if errorlevel 1 goto build

:build
echo.
echo Building production assets...
call npm run build
echo.
echo ✓ Build complete! Refresh browser (Ctrl+F5)
pause
goto end

:watch
echo.
echo Starting watch mode...
echo File akan di-rebuild otomatis saat ada perubahan
echo Tekan Ctrl+C untuk stop
echo.
call npm run build -- --watch
goto end

:devserver
echo.
echo ⚠️ WARNING: Dropdown akan bug dengan mode ini!
echo Tekan Ctrl+C untuk stop
echo.
call npm run dev
goto end

:end
