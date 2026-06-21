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

powershell -Command "& { [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; try { Write-Host '   Downloading PHP runtime...'; Invoke-WebRequest -Uri 'https://windows.php.net/downloads/releases/php-8.3.20-Win32-vs16-x64.zip' -OutFile '%APP_DIR%php.zip' -UseBasicParsing } catch { try { Invoke-WebRequest -Uri 'https://windows.php.net/downloads/releases/php-8.2.27-Win32-vs16-x64.zip' -OutFile '%APP_DIR%php.zip' -UseBasicParsing } catch { Write-Host '   Download failed. Check internet connection.'; exit 1 } } }"

if not exist "%APP_DIR%php.zip" (
    echo.
    echo   [ERROR] Download failed. Check your internet connection.
    echo   After downloading, try again.
    pause
    exit /b 1
)

echo   Extracting files...
powershell -Command "Expand-Archive -Path '%APP_DIR%php.zip' -DestinationPath '%PHP_DIR%' -Force"

:: Move files if extracted into subfolder
for /d %%D in ("%PHP_DIR%\php-*") do (
    xcopy "%%D\*" "%PHP_DIR%\" /E /Y /Q >nul 2>&1
    rmdir "%%D" /S /Q >nul 2>&1
)

:: Configure PHP
if exist "%PHP_DIR%\php.ini-development" (
    copy "%PHP_DIR%\php.ini-development" "%PHP_DIR%\php.ini" /Y >nul
)
powershell -Command "& { $f='%PHP_DIR%\php.ini'; if(Test-Path $f){$c=Get-Content $f -Raw; $c=$c-replace';extension=pdo_sqlite','extension=pdo_sqlite'; $c=$c-replace';extension=sqlite3','extension=sqlite3'; $c=$c-replace';extension=mbstring','extension=mbstring'; $c=$c-replace';extension=openssl','extension=openssl'; $c=$c-replace';extension=curl','extension=curl'; $c=$c-replace';extension=gd','extension=gd'; $c=$c-replace';extension=fileinfo','extension=fileinfo'; $c=$c-replace';extension_dir = \""ext\""','extension_dir = \""ext\""'; Set-Content $f $c}}"

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

:: Cleanup
del "%APP_DIR%php.zip" 2>nul

echo.
echo   Setup complete!
echo.

:: ========== START SERVER ==========
:start_server

:: Make sure .env exists
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

echo   Server starting at http://localhost:%PORT%
echo.
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
