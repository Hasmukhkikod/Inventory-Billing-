# ========================================================
# Grovixo IIMS v2.0 - Automated Installer Builder
# ========================================================
#
# This script prepares the build folder and creates Setup.exe
#
# PREREQUISITES:
#   1. Inno Setup installed (https://jrsoftware.org/isinfo.php)
#   2. Internet connection (to download PHP)
#   3. Run from the desktop/ folder
#
# USAGE:
#   Right-click > Run with PowerShell
#   OR: powershell -ExecutionPolicy Bypass -File build-installer.ps1
#

$ErrorActionPreference = "Stop"
$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$BuildDir = Join-Path $ScriptDir "build"
$AppSource = Split-Path -Parent $ScriptDir  # Parent = invoices project root
$PhpVersion = "8.3.20"
$PhpUrl = "https://windows.php.net/downloads/releases/php-$PhpVersion-Win32-vs16-x64.zip"
$PhpFallbackUrl = "https://windows.php.net/downloads/releases/php-8.2.27-Win32-vs16-x64.zip"

Write-Host "========================================================" -ForegroundColor Cyan
Write-Host "   Grovixo IIMS v2.0 - Installer Builder" -ForegroundColor Cyan
Write-Host "========================================================" -ForegroundColor Cyan
Write-Host ""

# ==================== STEP 1: Clean & Create Build Dir ====================
Write-Host "[1/5] Preparing build directory..." -ForegroundColor Yellow
if (Test-Path $BuildDir) { Remove-Item $BuildDir -Recurse -Force }
New-Item -ItemType Directory -Path $BuildDir -Force | Out-Null
New-Item -ItemType Directory -Path "$BuildDir\app" -Force | Out-Null
New-Item -ItemType Directory -Path "$BuildDir\php" -Force | Out-Null
New-Item -ItemType Directory -Path "$BuildDir\data" -Force | Out-Null
Write-Host "[OK] Build directory created." -ForegroundColor Green

# ==================== STEP 2: Download PHP ====================
Write-Host ""
Write-Host "[2/5] Downloading PHP $PhpVersion portable..." -ForegroundColor Yellow
$phpZip = Join-Path $ScriptDir "php-portable.zip"

if (-not (Test-Path $phpZip)) {
    try {
        [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
        Invoke-WebRequest -Uri $PhpUrl -OutFile $phpZip -UseBasicParsing
        Write-Host "[OK] PHP $PhpVersion downloaded." -ForegroundColor Green
    } catch {
        Write-Host "[WARN] PHP $PhpVersion failed. Trying fallback..." -ForegroundColor Yellow
        try {
            Invoke-WebRequest -Uri $PhpFallbackUrl -OutFile $phpZip -UseBasicParsing
            Write-Host "[OK] PHP fallback downloaded." -ForegroundColor Green
        } catch {
            Write-Host "[ERROR] Could not download PHP. Please download manually:" -ForegroundColor Red
            Write-Host "  URL: $PhpUrl" -ForegroundColor Red
            Write-Host "  Save as: $phpZip" -ForegroundColor Red
            Read-Host "Press Enter to exit"
            exit 1
        }
    }
} else {
    Write-Host "[SKIP] PHP ZIP already exists." -ForegroundColor Gray
}

Write-Host "[INFO] Extracting PHP..." -ForegroundColor Yellow
Expand-Archive -Path $phpZip -DestinationPath "$BuildDir\php" -Force

# Move files up if extracted into subfolder
$subDirs = Get-ChildItem "$BuildDir\php" -Directory -Filter "php-*"
foreach ($dir in $subDirs) {
    Copy-Item "$($dir.FullName)\*" "$BuildDir\php\" -Recurse -Force
    Remove-Item $dir.FullName -Recurse -Force
}
Write-Host "[OK] PHP extracted." -ForegroundColor Green

# ==================== STEP 3: Copy Application Files ====================
Write-Host ""
Write-Host "[3/5] Copying application files..." -ForegroundColor Yellow

$excludeDirs = @('.git', 'desktop', 'node_modules', '.DS_Store', '.env')
$items = Get-ChildItem $AppSource -Exclude $excludeDirs

foreach ($item in $items) {
    if ($item.Name -eq 'desktop') { continue }
    if ($item.Name -eq '.git') { continue }
    if ($item.Name -eq '.DS_Store') { continue }

    if ($item.PSIsContainer) {
        Copy-Item $item.FullName "$BuildDir\app\$($item.Name)" -Recurse -Force
    } else {
        Copy-Item $item.FullName "$BuildDir\app\$($item.Name)" -Force
    }
}

# Create .env for SQLite
@"
DB_DRIVER=sqlite
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=grovixo
DB_USER=root
DB_PASS=
"@ | Set-Content "$BuildDir\app\.env" -Encoding UTF8

Write-Host "[OK] Application files copied." -ForegroundColor Green

# ==================== STEP 4: Copy Launcher Scripts ====================
Write-Host ""
Write-Host "[4/5] Creating launcher scripts..." -ForegroundColor Yellow

# Create the start.bat for the installed location
@"
@echo off
title Grovixo IIMS - Billing Software
color 0A
echo.
echo   Starting Grovixo IIMS...
echo   Please wait, opening your browser...
echo.

set "APP_DIR=%~dp0app"
set "PHP_DIR=%~dp0php"
set "PORT=8000"

:: Kill any existing server on port
for /f "tokens=5" %%%%a in ('netstat -ano ^| findstr :%PORT% ^| findstr LISTENING 2^>nul') do (
    taskkill /F /PID %%%%a >nul 2>&1
)

:: Set SQLite environment
(
echo DB_DRIVER=sqlite
echo DB_HOST=127.0.0.1
echo DB_PORT=3306
echo DB_NAME=grovixo
echo DB_USER=root
echo DB_PASS=
) > "%APP_DIR%\.env"

:: Open browser after 2 seconds
start "" /b cmd /c "timeout /t 2 /nobreak >nul && start http://localhost:%PORT%"

:: Start server
echo   Server running at http://localhost:%PORT%
echo   Close this window to stop the server.
echo.
cd /d "%APP_DIR%"
"%PHP_DIR%\php.exe" -S localhost:%PORT% server.php
"@ | Set-Content "$BuildDir\start.bat" -Encoding ASCII

# Create stop.bat
@"
@echo off
echo Stopping Grovixo IIMS...
taskkill /F /IM php.exe >nul 2>&1
echo Server stopped.
timeout /t 2 /nobreak >nul
"@ | Set-Content "$BuildDir\stop.bat" -Encoding ASCII

Write-Host "[OK] Launcher scripts created." -ForegroundColor Green

# ==================== STEP 5: Compile Installer ====================
Write-Host ""
Write-Host "[5/5] Compiling installer..." -ForegroundColor Yellow

$innoPath = ""
$innoPaths = @(
    "C:\Program Files (x86)\Inno Setup 6\ISCC.exe",
    "C:\Program Files\Inno Setup 6\ISCC.exe",
    "C:\Program Files (x86)\Inno Setup 5\ISCC.exe"
)

foreach ($p in $innoPaths) {
    if (Test-Path $p) { $innoPath = $p; break }
}

if ($innoPath -ne "") {
    Write-Host "[INFO] Found Inno Setup at: $innoPath" -ForegroundColor Gray
    & $innoPath "$ScriptDir\installer.iss"
    Write-Host ""
    Write-Host "[OK] Installer compiled!" -ForegroundColor Green
    Write-Host "[OK] Setup.exe location: $ScriptDir\Output\Grovixo-IIMS-Setup-v2.0.exe" -ForegroundColor Green
} else {
    Write-Host "[WARN] Inno Setup not found. Install it from:" -ForegroundColor Yellow
    Write-Host "  https://jrsoftware.org/isdl.php" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "[INFO] Build folder is ready at: $BuildDir" -ForegroundColor Cyan
    Write-Host "[INFO] You can manually compile installer.iss with Inno Setup." -ForegroundColor Cyan
}

# ==================== DONE ====================
Write-Host ""
Write-Host "========================================================" -ForegroundColor Cyan
Write-Host "   BUILD COMPLETE!" -ForegroundColor Green
Write-Host "========================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "  Build folder: $BuildDir" -ForegroundColor White
Write-Host "  PHP version:  $PhpVersion" -ForegroundColor White
Write-Host "  Database:     SQLite (portable, no MySQL)" -ForegroundColor White
Write-Host "  App size:     ~$('{0:N0}' -f ((Get-ChildItem $BuildDir -Recurse | Measure-Object -Property Length -Sum).Sum / 1MB)) MB" -ForegroundColor White
Write-Host ""
Read-Host "Press Enter to exit"
