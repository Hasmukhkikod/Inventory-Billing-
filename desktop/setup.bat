@echo off
title Grovixo IIMS - First Time Setup
color 0B

echo ========================================================
echo    Grovixo IIMS v2.0 - First Time Setup
echo ========================================================
echo.
echo This will download and configure all required components.
echo No XAMPP or MySQL required - everything runs standalone!
echo.
pause

set "INSTALL_DIR=%~dp0"
set "PHP_DIR=%INSTALL_DIR%php"
set "APP_DIR=%INSTALL_DIR%..\app"
set "DATA_DIR=%INSTALL_DIR%data"
set "TEMP_DIR=%INSTALL_DIR%temp"

:: Create directories
if not exist "%DATA_DIR%" mkdir "%DATA_DIR%"
if not exist "%TEMP_DIR%" mkdir "%TEMP_DIR%"

:: ==================== STEP 1: Download PHP ====================
if exist "%PHP_DIR%\php.exe" (
    echo [SKIP] PHP already installed at %PHP_DIR%
    goto :php_done
)

echo.
echo [STEP 1/3] Downloading PHP 8.3 (portable)...
echo This may take a few minutes depending on your internet speed...

:: Download PHP 8.3 for Windows (Thread Safe, x64)
powershell -Command "& { [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; Invoke-WebRequest -Uri 'https://windows.php.net/downloads/releases/php-8.3.20-Win32-vs16-x64.zip' -OutFile '%TEMP_DIR%\php.zip' }" 2>nul

if not exist "%TEMP_DIR%\php.zip" (
    echo [WARN] Could not download PHP 8.3.20. Trying alternative version...
    powershell -Command "& { [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; Invoke-WebRequest -Uri 'https://windows.php.net/downloads/releases/php-8.2.27-Win32-vs16-x64.zip' -OutFile '%TEMP_DIR%\php.zip' }" 2>nul
)

if not exist "%TEMP_DIR%\php.zip" (
    echo.
    echo [ERROR] Failed to download PHP automatically.
    echo.
    echo MANUAL INSTALLATION:
    echo 1. Go to https://windows.php.net/download
    echo 2. Download "VS16 x64 Thread Safe" ZIP
    echo 3. Extract the ZIP contents into: %PHP_DIR%\
    echo 4. Run this setup again.
    echo.
    pause
    exit /b 1
)

echo [OK] PHP downloaded. Extracting...
mkdir "%PHP_DIR%" 2>nul
powershell -Command "Expand-Archive -Path '%TEMP_DIR%\php.zip' -DestinationPath '%PHP_DIR%' -Force"

:: If PHP extracted into a subfolder, move files up
for /d %%D in ("%PHP_DIR%\php-*") do (
    xcopy "%%D\*" "%PHP_DIR%\" /E /Y /Q >nul 2>&1
    rmdir "%%D" /S /Q >nul 2>&1
)

if not exist "%PHP_DIR%\php.exe" (
    echo [ERROR] PHP extraction failed. Please install manually.
    pause
    exit /b 1
)

echo [OK] PHP installed successfully!

:php_done

:: ==================== STEP 2: Configure PHP ====================
echo.
echo [STEP 2/3] Configuring PHP for Grovixo...

:: Create php.ini from development template
if exist "%PHP_DIR%\php.ini-development" (
    copy "%PHP_DIR%\php.ini-development" "%PHP_DIR%\php.ini" /Y >nul
)

:: Enable required extensions
powershell -Command "& { $ini = '%PHP_DIR%\php.ini'; if (Test-Path $ini) { $c = Get-Content $ini -Raw; $c = $c -replace ';extension=pdo_sqlite', 'extension=pdo_sqlite'; $c = $c -replace ';extension=sqlite3', 'extension=sqlite3'; $c = $c -replace ';extension=pdo_mysql', 'extension=pdo_mysql'; $c = $c -replace ';extension=mbstring', 'extension=mbstring'; $c = $c -replace ';extension=openssl', 'extension=openssl'; $c = $c -replace ';extension=curl', 'extension=curl'; $c = $c -replace ';extension=gd', 'extension=gd'; $c = $c -replace ';extension=fileinfo', 'extension=fileinfo'; $c = $c -replace ';extension_dir = \"ext\"', 'extension_dir = \"ext\"'; Set-Content $ini $c; } }"

echo [OK] PHP configured with required extensions.

:: ==================== STEP 3: Setup Application ====================
echo.
echo [STEP 3/3] Setting up Grovixo application...

:: Create .env for SQLite mode
(
echo DB_DRIVER=sqlite
echo DB_HOST=127.0.0.1
echo DB_PORT=3306
echo DB_NAME=grovixo
echo DB_USER=root
echo DB_PASS=
) > "%APP_DIR%\.env"

:: Install Composer dependencies if needed
if not exist "%APP_DIR%\vendor" (
    echo [INFO] Installing PHP dependencies...
    cd /d "%APP_DIR%"
    "%PHP_DIR%\php.exe" -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    "%PHP_DIR%\php.exe" composer-setup.php --quiet
    "%PHP_DIR%\php.exe" composer.phar install --no-dev --optimize-autoloader --quiet 2>nul
    del composer-setup.php 2>nul
    del composer.phar 2>nul
    echo [OK] Dependencies installed.
) else (
    echo [SKIP] Dependencies already installed.
)

:: Clean up temp files
rmdir "%TEMP_DIR%" /S /Q 2>nul

echo.
echo ========================================================
echo    SETUP COMPLETE!
echo ========================================================
echo.
echo  To start Grovixo IIMS:
echo    Double-click "Start Grovixo.bat"
echo.
echo  Login Credentials:
echo    Email:    hasmukhkikod@gmail.com
echo    Password: admin123
echo.
echo  Your data is stored locally in SQLite.
echo  No internet required to run the software.
echo ========================================================
echo.
pause
