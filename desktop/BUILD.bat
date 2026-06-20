@echo off
title Grovixo IIMS - Building Client Package...
color 0B

echo.
echo  ============================================================
echo    GROVIXO IIMS v2.0 - Client Package Builder
echo  ============================================================
echo.
echo   This will create a ready-to-share ZIP for your clients.
echo   Internet required (to download PHP runtime ~30MB).
echo.
echo   Press any key to start building...
pause >nul

set "SCRIPT_DIR=%~dp0"
set "PROJECT_DIR=%SCRIPT_DIR%.."
set "BUILD_DIR=%SCRIPT_DIR%_build"
set "OUTPUT_DIR=%BUILD_DIR%\Grovixo-IIMS"
set "PHP_ZIP=%SCRIPT_DIR%php-portable.zip"
set "FINAL_ZIP=%SCRIPT_DIR%Grovixo-IIMS-Portable-v2.0.zip"

:: ==================== CLEAN ====================
echo.
echo [1/6] Cleaning previous build...
if exist "%BUILD_DIR%" rmdir /S /Q "%BUILD_DIR%"
if exist "%FINAL_ZIP%" del /F "%FINAL_ZIP%"
mkdir "%OUTPUT_DIR%"
mkdir "%OUTPUT_DIR%\app"
mkdir "%OUTPUT_DIR%\php"
mkdir "%OUTPUT_DIR%\app\database"
mkdir "%OUTPUT_DIR%\app\uploads"
mkdir "%OUTPUT_DIR%\app\backups"
mkdir "%OUTPUT_DIR%\app\logs"
echo       [OK] Clean build directory ready.

:: ==================== DOWNLOAD PHP ====================
echo.
echo [2/6] Downloading PHP 8.3 portable for Windows...
echo       (This may take 1-3 minutes on first run)
echo.

if exist "%PHP_ZIP%" (
    echo       [SKIP] PHP already downloaded. Using cached copy.
    goto :extract_php
)

powershell -Command "& { [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; try { Invoke-WebRequest -Uri 'https://windows.php.net/downloads/releases/php-8.3.20-Win32-vs16-x64.zip' -OutFile '%PHP_ZIP%' -UseBasicParsing } catch { try { Invoke-WebRequest -Uri 'https://windows.php.net/downloads/releases/php-8.2.27-Win32-vs16-x64.zip' -OutFile '%PHP_ZIP%' -UseBasicParsing } catch { Write-Host 'DOWNLOAD FAILED' } } }"

if not exist "%PHP_ZIP%" (
    echo.
    echo  [ERROR] Could not download PHP. Check your internet connection.
    echo.
    echo  MANUAL FIX:
    echo  1. Open browser: https://windows.php.net/download
    echo  2. Download "VS16 x64 Thread Safe" ZIP file
    echo  3. Save it as: %PHP_ZIP%
    echo  4. Run this script again.
    echo.
    pause
    exit /b 1
)

:extract_php
echo       [OK] Extracting PHP...
powershell -Command "Expand-Archive -Path '%PHP_ZIP%' -DestinationPath '%OUTPUT_DIR%\php' -Force"

:: Move files up if in subfolder
for /d %%D in ("%OUTPUT_DIR%\php\php-*") do (
    xcopy "%%D\*" "%OUTPUT_DIR%\php\" /E /Y /Q >nul 2>&1
    rmdir "%%D" /S /Q >nul 2>&1
)

if not exist "%OUTPUT_DIR%\php\php.exe" (
    echo  [ERROR] PHP extraction failed!
    pause
    exit /b 1
)
echo       [OK] PHP ready.

:: ==================== CONFIGURE PHP ====================
echo.
echo [3/6] Configuring PHP extensions...

if exist "%OUTPUT_DIR%\php\php.ini-development" (
    copy "%OUTPUT_DIR%\php\php.ini-development" "%OUTPUT_DIR%\php\php.ini" /Y >nul
)

powershell -Command "& { $ini = '%OUTPUT_DIR%\php\php.ini'; if (Test-Path $ini) { $c = Get-Content $ini -Raw; $c = $c -replace ';extension=pdo_sqlite', 'extension=pdo_sqlite'; $c = $c -replace ';extension=sqlite3', 'extension=sqlite3'; $c = $c -replace ';extension=pdo_mysql', 'extension=pdo_mysql'; $c = $c -replace ';extension=mbstring', 'extension=mbstring'; $c = $c -replace ';extension=openssl', 'extension=openssl'; $c = $c -replace ';extension=curl', 'extension=curl'; $c = $c -replace ';extension=gd', 'extension=gd'; $c = $c -replace ';extension=fileinfo', 'extension=fileinfo'; $c = $c -replace ';extension_dir = \""ext\""', 'extension_dir = \""ext\""'; Set-Content $ini $c; } }"

echo       [OK] PHP configured.

:: ==================== COPY APPLICATION ====================
echo.
echo [4/6] Copying Grovixo application files...

:: Copy all app files except .git and desktop folders
for %%F in ("%PROJECT_DIR%\*") do (
    if /i not "%%~nxF"==".git" if /i not "%%~nxF"=="desktop" if /i not "%%~nxF"==".DS_Store" if /i not "%%~nxF"==".env" (
        copy "%%F" "%OUTPUT_DIR%\app\" /Y >nul 2>&1
    )
)

for /d %%D in ("%PROJECT_DIR%\*") do (
    if /i not "%%~nxD"==".git" if /i not "%%~nxD"=="desktop" if /i not "%%~nxD"=="node_modules" (
        xcopy "%%D" "%OUTPUT_DIR%\app\%%~nxD\" /E /Y /Q >nul 2>&1
    )
)

:: Create .env for SQLite (no MySQL needed)
(
echo DB_DRIVER=sqlite
echo DB_HOST=127.0.0.1
echo DB_PORT=3306
echo DB_NAME=grovixo
echo DB_USER=root
echo DB_PASS=
) > "%OUTPUT_DIR%\app\.env"

echo       [OK] Application files copied.

:: ==================== CREATE LAUNCHER SCRIPTS ====================
echo.
echo [5/6] Creating launcher scripts...

:: --- Start Grovixo.bat ---
(
echo @echo off
echo title Grovixo IIMS - Billing Software
echo color 0A
echo echo.
echo echo   ========================================
echo echo    Grovixo IIMS v2.0
echo echo    Invoice ^& Inventory Management System
echo echo   ========================================
echo echo.
echo echo   Starting server... please wait.
echo echo.
echo.
echo set "APP_DIR=%%~dp0app"
echo set "PHP_DIR=%%~dp0php"
echo set "PORT=8000"
echo.
echo :: Kill existing process on port
echo for /f "tokens=5" %%%%a in ('netstat -ano ^^^| findstr :%%PORT%% ^^^| findstr LISTENING 2^^^>nul'^) do (
echo     taskkill /F /PID %%%%a ^>nul 2^>^&1
echo ^)
echo.
echo :: Open browser after 2 seconds
echo start "" /b cmd /c "timeout /t 2 /nobreak ^>nul ^&^& start http://localhost:%%PORT%%"
echo.
echo echo   Browser opening at: http://localhost:%%PORT%%
echo echo.
echo echo   Keep this window open while using the software.
echo echo   Close this window to stop the server.
echo echo.
echo.
echo cd /d "%%APP_DIR%%"
echo "%%PHP_DIR%%\php.exe" -S localhost:%%PORT%% server.php
) > "%OUTPUT_DIR%\Start Grovixo.bat"

:: --- Stop Grovixo.bat ---
(
echo @echo off
echo echo Stopping Grovixo IIMS...
echo taskkill /F /IM php.exe ^>nul 2^>^&1
echo echo Server stopped.
echo timeout /t 2 /nobreak ^>nul
) > "%OUTPUT_DIR%\Stop Grovixo.bat"

:: --- README.txt ---
(
echo =============================================
echo   GROVIXO IIMS v2.0 - Quick Start Guide
echo =============================================
echo.
echo   HOW TO USE:
echo   1. Double-click "Start Grovixo.bat"
echo   2. Browser opens automatically
echo   3. Login and start billing!
echo.
echo   TO STOP:
echo   Close the black command window
echo   OR double-click "Stop Grovixo.bat"
echo.
echo   REQUIREMENTS:
echo   - Windows 10/11 ^(64-bit^)
echo   - No internet needed
echo   - No other software needed
echo.
echo   YOUR DATA:
echo   Stored in: app\database\database.sqlite
echo   Always backup this file regularly!
echo.
echo   TROUBLESHOOTING:
echo   - "Windows protected your PC" popup?
echo     Click "More info" then "Run anyway"
echo   - Browser not opening?
echo     Go to http://localhost:8000 manually
echo.
echo   Powered by Grovixo IIMS v2.0
echo =============================================
) > "%OUTPUT_DIR%\README.txt"

:: Copy client guide HTML
if exist "%SCRIPT_DIR%CLIENT_GUIDE.html" (
    copy "%SCRIPT_DIR%CLIENT_GUIDE.html" "%OUTPUT_DIR%\Installation Guide.html" /Y >nul
)

echo       [OK] Launchers and guides created.

:: ==================== CREATE ZIP ====================
echo.
echo [6/6] Creating final ZIP package...

powershell -Command "Compress-Archive -Path '%OUTPUT_DIR%' -DestinationPath '%FINAL_ZIP%' -CompressionLevel Optimal -Force"

if not exist "%FINAL_ZIP%" (
    echo  [ERROR] Failed to create ZIP.
    echo  The build folder is ready at: %OUTPUT_DIR%
    echo  You can manually ZIP it.
    pause
    exit /b 1
)

:: Get file size
for %%A in ("%FINAL_ZIP%") do set "ZIP_SIZE=%%~zA"
set /a ZIP_MB=%ZIP_SIZE% / 1048576

:: Clean build folder
rmdir "%BUILD_DIR%" /S /Q >nul 2>&1

echo       [OK] ZIP created successfully!

echo.
echo  ============================================================
echo    BUILD COMPLETE!
echo  ============================================================
echo.
echo   Your client package is ready:
echo.
echo   %FINAL_ZIP%
echo   Size: ~%ZIP_MB% MB
echo.
echo  ============================================================
echo   WHAT TO SEND TO CLIENT:
echo  ============================================================
echo.
echo   1. Send this ZIP file to your client
echo      (via email, USB, Google Drive, WhatsApp, etc.)
echo.
echo   2. Tell them to:
echo      - Extract the ZIP
echo      - Double-click "Start Grovixo.bat"
echo      - Login and use!
echo.
echo   That's it! No XAMPP, no MySQL, no setup needed.
echo  ============================================================
echo.

:: Open the folder containing the ZIP
explorer /select,"%FINAL_ZIP%"

pause
