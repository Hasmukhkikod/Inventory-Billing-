# ========================================================
# Grovixo IIMS v2.0 - Portable Version Builder
# ========================================================
#
# Creates a portable ZIP that clients can extract and run.
# No installer needed - just extract, double-click start.bat
#
# USAGE:
#   powershell -ExecutionPolicy Bypass -File build-portable.ps1
#

$ErrorActionPreference = "Stop"
$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$AppSource = Split-Path -Parent $ScriptDir
$OutputDir = Join-Path $ScriptDir "portable"
$PortableDir = Join-Path $OutputDir "Grovixo-IIMS"
$PhpUrl = "https://windows.php.net/downloads/releases/php-8.3.20-Win32-vs16-x64.zip"

Write-Host "========================================================" -ForegroundColor Cyan
Write-Host "   Grovixo IIMS - Portable Version Builder" -ForegroundColor Cyan
Write-Host "========================================================" -ForegroundColor Cyan
Write-Host ""

# Clean
if (Test-Path $OutputDir) { Remove-Item $OutputDir -Recurse -Force }
New-Item -ItemType Directory -Path $PortableDir -Force | Out-Null

# Download PHP
Write-Host "[1/4] Downloading PHP..." -ForegroundColor Yellow
$phpZip = Join-Path $ScriptDir "php-portable.zip"
if (-not (Test-Path $phpZip)) {
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
    Invoke-WebRequest -Uri $PhpUrl -OutFile $phpZip -UseBasicParsing
}
New-Item -ItemType Directory -Path "$PortableDir\php" -Force | Out-Null
Expand-Archive -Path $phpZip -DestinationPath "$PortableDir\php" -Force
$subDirs = Get-ChildItem "$PortableDir\php" -Directory -Filter "php-*"
foreach ($dir in $subDirs) {
    Copy-Item "$($dir.FullName)\*" "$PortableDir\php\" -Recurse -Force
    Remove-Item $dir.FullName -Recurse -Force
}
Write-Host "[OK] PHP ready." -ForegroundColor Green

# Copy app
Write-Host "[2/4] Copying application..." -ForegroundColor Yellow
New-Item -ItemType Directory -Path "$PortableDir\app" -Force | Out-Null
Get-ChildItem $AppSource -Exclude @('.git','desktop','.DS_Store') | ForEach-Object {
    if ($_.Name -ne 'desktop' -and $_.Name -ne '.git') {
        Copy-Item $_.FullName "$PortableDir\app\$($_.Name)" -Recurse -Force
    }
}
Write-Host "[OK] App copied." -ForegroundColor Green

# Create configs
Write-Host "[3/4] Creating configs..." -ForegroundColor Yellow
@"
DB_DRIVER=sqlite
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=grovixo
DB_USER=root
DB_PASS=
"@ | Set-Content "$PortableDir\app\.env" -Encoding UTF8

# Configure php.ini
Copy-Item "$PortableDir\php\php.ini-development" "$PortableDir\php\php.ini" -Force -ErrorAction SilentlyContinue
$ini = "$PortableDir\php\php.ini"
if (Test-Path $ini) {
    $c = Get-Content $ini -Raw
    $c = $c -replace ';extension=pdo_sqlite', 'extension=pdo_sqlite'
    $c = $c -replace ';extension=sqlite3', 'extension=sqlite3'
    $c = $c -replace ';extension=pdo_mysql', 'extension=pdo_mysql'
    $c = $c -replace ';extension=mbstring', 'extension=mbstring'
    $c = $c -replace ';extension=openssl', 'extension=openssl'
    $c = $c -replace ';extension=curl', 'extension=curl'
    $c = $c -replace ';extension=gd', 'extension=gd'
    $c = $c -replace ';extension=fileinfo', 'extension=fileinfo'
    $c = $c -replace ';extension_dir = "ext"', 'extension_dir = "ext"'
    Set-Content $ini $c
}

# Create launcher
@"
@echo off
title Grovixo IIMS - Billing Software
color 0A
echo.
echo   ========================================
echo    Grovixo IIMS v2.0
echo    Invoice ^& Inventory Management System
echo   ========================================
echo.
echo   Starting server... please wait.
echo.

set "APP_DIR=%~dp0app"
set "PHP_DIR=%~dp0php"
set "PORT=8000"

for /f "tokens=5" %%%%a in ('netstat -ano ^| findstr :%PORT% ^| findstr LISTENING 2^>nul') do (
    taskkill /F /PID %%%%a >nul 2>&1
)

start "" /b cmd /c "timeout /t 2 /nobreak >nul && start http://localhost:%PORT%"

echo   Browser opening at: http://localhost:%PORT%
echo.
echo   Login: hasmukhkikod@gmail.com / admin123
echo.
echo   Keep this window open while using the software.
echo   Close this window to stop the server.
echo.

cd /d "%APP_DIR%"
"%PHP_DIR%\php.exe" -S localhost:%PORT% server.php
"@ | Set-Content "$PortableDir\Start Grovixo.bat" -Encoding ASCII

@"
@echo off
taskkill /F /IM php.exe >nul 2>&1
echo Grovixo stopped.
timeout /t 2 /nobreak >nul
"@ | Set-Content "$PortableDir\Stop Grovixo.bat" -Encoding ASCII

# Create README
@"
GROVIXO IIMS v2.0 - Portable Edition
=====================================

HOW TO USE:
1. Double-click "Start Grovixo.bat"
2. Browser opens automatically
3. Login with: hasmukhkikod@gmail.com / admin123
4. To stop: Close the command window OR double-click "Stop Grovixo.bat"

REQUIREMENTS:
- Windows 10/11 (64-bit)
- No internet required after first setup
- No XAMPP, MySQL, or any other software needed

DATA:
- Your data is stored locally in app/database/database.sqlite
- Back up this file regularly to protect your data

SUPPORT:
- Contact your software provider for assistance
"@ | Set-Content "$PortableDir\README.txt" -Encoding UTF8

New-Item -ItemType Directory -Path "$PortableDir\app\database" -Force | Out-Null
New-Item -ItemType Directory -Path "$PortableDir\app\uploads" -Force | Out-Null
New-Item -ItemType Directory -Path "$PortableDir\app\backups" -Force | Out-Null
New-Item -ItemType Directory -Path "$PortableDir\app\logs" -Force | Out-Null

Write-Host "[OK] Configs created." -ForegroundColor Green

# Create ZIP
Write-Host "[4/4] Creating portable ZIP..." -ForegroundColor Yellow
$zipPath = Join-Path $OutputDir "Grovixo-IIMS-Portable-v2.0.zip"
if (Test-Path $zipPath) { Remove-Item $zipPath -Force }
Compress-Archive -Path $PortableDir -DestinationPath $zipPath -CompressionLevel Optimal
Write-Host "[OK] Portable ZIP created!" -ForegroundColor Green

$sizeMB = '{0:N1}' -f ((Get-Item $zipPath).Length / 1MB)
Write-Host ""
Write-Host "========================================================" -ForegroundColor Cyan
Write-Host "   PORTABLE BUILD COMPLETE!" -ForegroundColor Green
Write-Host "========================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "  Output: $zipPath" -ForegroundColor White
Write-Host "  Size:   $sizeMB MB" -ForegroundColor White
Write-Host ""
Write-Host "  Share this ZIP with your clients." -ForegroundColor White
Write-Host "  They just extract and double-click 'Start Grovixo.bat'" -ForegroundColor White
Write-Host ""
Read-Host "Press Enter to exit"
