@echo off
title Grovixo - Billing Software
color 0A
echo.
echo   ==========================================
echo     Grovixo - Billing Software
echo   ==========================================
echo.

set "APP_DIR=%~dp0"
set "PHP_DIR=%APP_DIR%php"
set "PORT=8000"

:: ========== CHECK IF PHP EXISTS ==========
if exist "%PHP_DIR%\php.exe" goto :start_server

:: ========== FIRST TIME: DOWNLOAD PHP ==========
echo   First time setup - downloading required files...
echo   This will take 1-2 minutes. Please wait...
echo.

mkdir "%PHP_DIR%" 2>nul

:: Try multiple download methods for compatibility with all Windows versions

:: Method 1: certutil (works on Windows 7, 8, 10, 11)
echo   Downloading PHP runtime (Method 1)...
certutil -urlcache -split -f "https://windows.php.net/downloads/releases/php-8.3.20-Win32-vs16-x64.zip" "%APP_DIR%php.zip" >nul 2>&1

if exist "%APP_DIR%php.zip" goto :extract_php

:: Method 2: bitsadmin (works on older Windows)
echo   Trying alternative download method...
bitsadmin /transfer phpdownload /priority high "https://windows.php.net/downloads/releases/php-8.3.20-Win32-vs16-x64.zip" "%APP_DIR%php.zip" >nul 2>&1

if exist "%APP_DIR%php.zip" goto :extract_php

:: Method 3: PowerShell (newer Windows)
echo   Trying PowerShell download...
powershell -Command "Invoke-WebRequest -Uri 'https://windows.php.net/downloads/releases/php-8.3.20-Win32-vs16-x64.zip' -OutFile '%APP_DIR%php.zip'" >nul 2>&1

if exist "%APP_DIR%php.zip" goto :extract_php

:: Try PHP 8.2 as fallback
echo   Trying alternative PHP version...
certutil -urlcache -split -f "https://windows.php.net/downloads/releases/php-8.2.27-Win32-vs16-x64.zip" "%APP_DIR%php.zip" >nul 2>&1

if exist "%APP_DIR%php.zip" goto :extract_php

:: All methods failed
echo.
echo   ==========================================
echo   DOWNLOAD FAILED
echo   ==========================================
echo.
echo   Could not download PHP automatically.
echo.
echo   PLEASE DO THIS MANUALLY:
echo   1. Open your browser
echo   2. Go to: https://windows.php.net/download
echo   3. Download "VS16 x64 Thread Safe" ZIP file
echo   4. Save it as "php.zip" inside this folder:
echo      %APP_DIR%
echo   5. Run START.bat again
echo.
pause
exit /b 1

:: ========== EXTRACT PHP ==========
:extract_php
echo   Extracting PHP...

:: Try PowerShell extraction first
powershell -Command "Expand-Archive -Path '%APP_DIR%php.zip' -DestinationPath '%PHP_DIR%' -Force" >nul 2>&1

:: Check if extraction worked
if exist "%PHP_DIR%\php.exe" goto :configure_php

:: Files might be in a subfolder
for /d %%D in ("%PHP_DIR%\php-*") do (
    xcopy "%%D\*" "%PHP_DIR%\" /E /Y /Q >nul 2>&1
    rmdir "%%D" /S /Q >nul 2>&1
)

if exist "%PHP_DIR%\php.exe" goto :configure_php

:: Try VBS extraction for older Windows without PowerShell
echo   Using alternative extraction...
echo Set objShell = CreateObject("Shell.Application") > "%APP_DIR%unzip.vbs"
echo Set FilesInZip = objShell.NameSpace("%APP_DIR%php.zip").items >> "%APP_DIR%unzip.vbs"
echo objShell.NameSpace("%PHP_DIR%").CopyHere FilesInZip, 16 >> "%APP_DIR%unzip.vbs"
cscript //nologo "%APP_DIR%unzip.vbs"
del "%APP_DIR%unzip.vbs" 2>nul

:: Move from subfolder if needed
for /d %%D in ("%PHP_DIR%\php-*") do (
    xcopy "%%D\*" "%PHP_DIR%\" /E /Y /Q >nul 2>&1
    rmdir "%%D" /S /Q >nul 2>&1
)

if not exist "%PHP_DIR%\php.exe" (
    echo.
    echo   [ERROR] Extraction failed!
    echo   Please extract php.zip manually into the "php" folder.
    pause
    exit /b 1
)

:: ========== CONFIGURE PHP ==========
:configure_php
echo   Configuring...

:: Create php.ini
if exist "%PHP_DIR%\php.ini-development" (
    copy "%PHP_DIR%\php.ini-development" "%PHP_DIR%\php.ini" /Y >nul
)

:: Enable extensions using simple text replacement
if exist "%PHP_DIR%\php.ini" (
    powershell -Command "$f='%PHP_DIR%\php.ini'; $c=Get-Content $f -Raw; $c=$c-replace';extension=pdo_sqlite','extension=pdo_sqlite'; $c=$c-replace';extension=sqlite3','extension=sqlite3'; $c=$c-replace';extension=mbstring','extension=mbstring'; $c=$c-replace';extension=openssl','extension=openssl'; $c=$c-replace';extension=curl','extension=curl'; $c=$c-replace';extension=gd','extension=gd'; $c=$c-replace';extension=fileinfo','extension=fileinfo'; $c=$c-replace';extension_dir = \"ext\"','extension_dir = \"ext\"'; Set-Content $f $c" >nul 2>&1
)

:: Create .env for SQLite
(
echo DB_DRIVER=sqlite
echo DB_HOST=127.0.0.1
echo DB_PORT=3306
echo DB_NAME=grovixo
echo DB_USER=root
echo DB_PASS=
) > "%APP_DIR%.env"

:: Install composer dependencies if needed
if not exist "%APP_DIR%vendor" (
    echo   Installing dependencies...
    cd /d "%APP_DIR%"
    "%PHP_DIR%\php.exe" -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" 2>nul
    "%PHP_DIR%\php.exe" composer-setup.php --quiet 2>nul
    "%PHP_DIR%\php.exe" composer.phar install --no-dev --quiet 2>nul
    del composer-setup.php 2>nul
    del composer.phar 2>nul
)

:: Cleanup zip
del "%APP_DIR%php.zip" 2>nul

echo.
echo   Setup complete!
echo.

:: ========== START SERVER ==========
:start_server

:: Ensure .env exists
if not exist "%APP_DIR%.env" (
    (
    echo DB_DRIVER=sqlite
    echo DB_HOST=127.0.0.1
    echo DB_PORT=3306
    echo DB_NAME=grovixo
    echo DB_USER=root
    echo DB_PASS=
    ) > "%APP_DIR%.env"
)

:: Kill any existing server on port
for /f "tokens=5" %%a in ('netstat -ano ^| findstr :%PORT% ^| findstr LISTENING 2^>nul') do (
    taskkill /F /PID %%a >nul 2>&1
)

echo   ==========================================
echo     Software is running!
echo     Browser will open automatically.
echo.
echo     DO NOT close this window while using.
echo     To stop: just close this window.
echo   ==========================================
echo.

:: Open browser after 2 seconds
start "" /b cmd /c "timeout /t 2 /nobreak >nul && start http://localhost:%PORT%"

:: Start PHP server
cd /d "%APP_DIR%"
"%PHP_DIR%\php.exe" -S localhost:%PORT% server.php
