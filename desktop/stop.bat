@echo off
title Grovixo IIMS - Stopping Server
echo Stopping Grovixo IIMS Server...

:: Kill PHP processes running our server
taskkill /F /IM php.exe >nul 2>&1

echo [OK] Server stopped successfully.
timeout /t 2 /nobreak >nul
