@echo off
title Grovixo - Billing Software
color 0A
echo.
echo   ==========================================
echo     Grovixo - Billing Software v2.0
echo   ==========================================
echo.

set "APP_DIR=%~dp0"
set "PHP_DIR=%APP_DIR%php"
set "PORT=8000"

if not exist "%PHP_DIR%\php.exe" (
    echo   [ERROR] PHP not found in "php" folder.
    echo   Please contact your software provider.
    echo.
    pause
    exit /b 1
)

if not exist "%APP_DIR%.env" (
    echo DB_DRIVER=sqlite> "%APP_DIR%.env"
    echo DB_HOST=127.0.0.1>> "%APP_DIR%.env"
    echo DB_PORT=3306>> "%APP_DIR%.env"
    echo DB_NAME=grovixo>> "%APP_DIR%.env"
    echo DB_USER=root>> "%APP_DIR%.env"
    echo DB_PASS=>> "%APP_DIR%.env"
)

echo   Starting Grovixo... please wait.
echo.
echo   ==========================================
echo     Software is running!
echo     Browser will open automatically.
echo.
echo     DO NOT close this window while using.
echo     To stop: just close this window.
echo   ==========================================
echo.

start "" "http://localhost:%PORT%"

cd /d "%APP_DIR%"
"%PHP_DIR%\php.exe" -S localhost:%PORT% server.php
