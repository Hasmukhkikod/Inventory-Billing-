@echo off
title Grovixo IIMS - Invoice & Inventory Management System
color 0A

echo ========================================================
echo    Grovixo IIMS v2.0 - Starting Application...
echo ========================================================
echo.

:: Set paths
set "APP_DIR=%~dp0..\app"
set "PHP_DIR=%~dp0php"
set "PORT=8000"
set "DATA_DIR=%~dp0data"

:: Check if PHP exists
if not exist "%PHP_DIR%\php.exe" (
    echo [ERROR] PHP not found at %PHP_DIR%
    echo Please run setup.bat first to install required components.
    pause
    exit /b 1
)

:: Create data directory if not exists
if not exist "%DATA_DIR%" mkdir "%DATA_DIR%"

:: Set environment for SQLite (no MySQL needed)
set "DB_DRIVER=sqlite"
set "DB_HOST=127.0.0.1"
set "DB_PORT=3306"
set "DB_NAME=grovixo"
set "DB_USER=root"
set "DB_PASS="

:: Write .env file for the app
(
echo DB_DRIVER=sqlite
echo DB_HOST=127.0.0.1
echo DB_PORT=3306
echo DB_NAME=grovixo
echo DB_USER=root
echo DB_PASS=
) > "%APP_DIR%\.env"

:: Kill any existing PHP server on this port
for /f "tokens=5" %%a in ('netstat -ano ^| findstr :%PORT% ^| findstr LISTENING 2^>nul') do (
    taskkill /F /PID %%a >nul 2>&1
)

echo [OK] Starting Grovixo Server on port %PORT%...
echo [OK] Database: SQLite (no MySQL required)
echo.
echo --------------------------------------------------------
echo    Application URL: http://localhost:%PORT%
echo    Press Ctrl+C or close this window to stop
echo --------------------------------------------------------
echo.

:: Wait 1 second then open browser
start "" /b cmd /c "timeout /t 2 /nobreak >nul && start http://localhost:%PORT%"

:: Start PHP built-in server
cd /d "%APP_DIR%"
"%PHP_DIR%\php.exe" -S localhost:%PORT% server.php

:: If we get here, server stopped
echo.
echo [INFO] Server stopped.
pause
