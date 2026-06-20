; ========================================================
; Grovixo IIMS v2.0 - Inno Setup Installer Script
; ========================================================
;
; HOW TO BUILD THE INSTALLER:
; 1. Install Inno Setup from https://jrsoftware.org/isinfo.php
; 2. Prepare the build folder (see BUILD_INSTRUCTIONS.txt)
; 3. Open this .iss file in Inno Setup Compiler
; 4. Click Build > Compile
; 5. The Setup.exe will be created in the Output folder
;
; FOLDER STRUCTURE REQUIRED BEFORE BUILDING:
;   desktop/
;   ├── build/
;   │   ├── app/          <-- Copy your entire invoices project here (minus .git, desktop/)
;   │   ├── php/          <-- Extract PHP portable here (php.exe, ext/, etc.)
;   │   ├── start.bat     <-- The launcher script
;   │   ├── stop.bat      <-- The stop script
;   │   └── data/         <-- Empty folder for SQLite database
;   ├── installer.iss     <-- This file
;   └── icon.ico          <-- Application icon (optional)

[Setup]
AppName=Grovixo IIMS
AppVersion=2.0
AppPublisher=Grovixo
AppPublisherURL=https://www.grovixo.com
DefaultDirName={autopf}\Grovixo IIMS
DefaultGroupName=Grovixo IIMS
OutputDir=Output
OutputBaseFilename=Grovixo-IIMS-Setup-v2.0
; Uncomment below if you have an icon file:
; SetupIconFile=icon.ico
Compression=lzma2/ultra64
SolidCompression=yes
WizardStyle=modern
PrivilegesRequired=lowest
DisableProgramGroupPage=yes
LicenseFile=
UninstallDisplayIcon={app}\app\assets\images\favicon.png
ArchitecturesInstallIn64BitMode=x64compatible

[Languages]
Name: "english"; MessagesFile: "compiler:Default.isl"

[Files]
; PHP Runtime (portable)
Source: "build\php\*"; DestDir: "{app}\php"; Flags: ignoreversion recursesubdirs createallsubdirs

; Application Files
Source: "build\app\*"; DestDir: "{app}\app"; Flags: ignoreversion recursesubdirs createallsubdirs; Excludes: "*.git*,desktop,node_modules,.DS_Store"

; Launcher Scripts
Source: "build\start.bat"; DestDir: "{app}"; Flags: ignoreversion
Source: "build\stop.bat"; DestDir: "{app}"; Flags: ignoreversion

[Dirs]
Name: "{app}\data"; Permissions: everyone-full
Name: "{app}\app\uploads"; Permissions: everyone-full
Name: "{app}\app\backups"; Permissions: everyone-full
Name: "{app}\app\logs"; Permissions: everyone-full
Name: "{app}\app\database"; Permissions: everyone-full

[Icons]
Name: "{group}\Grovixo IIMS"; Filename: "{app}\start.bat"; WorkingDir: "{app}"; Comment: "Start Grovixo Billing System"
Name: "{group}\Stop Grovixo"; Filename: "{app}\stop.bat"; WorkingDir: "{app}"; Comment: "Stop Grovixo Server"
Name: "{group}\Uninstall Grovixo"; Filename: "{uninstallexe}"
Name: "{autodesktop}\Grovixo IIMS"; Filename: "{app}\start.bat"; WorkingDir: "{app}"; Comment: "Start Grovixo Billing System"

[Run]
; Configure PHP after install
Filename: "{app}\php\php.exe"; Parameters: "-r ""copy('{app}\php\php.ini-development', '{app}\php\php.ini');"""; Flags: runhidden; StatusMsg: "Configuring PHP..."

; Create .env file for SQLite mode
Filename: "cmd.exe"; Parameters: "/c echo DB_DRIVER=sqlite> ""{app}\app\.env"" && echo DB_HOST=127.0.0.1>> ""{app}\app\.env"" && echo DB_PORT=3306>> ""{app}\app\.env"" && echo DB_NAME=grovixo>> ""{app}\app\.env"" && echo DB_USER=root>> ""{app}\app\.env"" && echo DB_PASS=>> ""{app}\app\.env"""; Flags: runhidden; StatusMsg: "Setting up database configuration..."

; Enable PHP extensions
Filename: "powershell.exe"; Parameters: "-ExecutionPolicy Bypass -Command ""$ini='{app}\php\php.ini'; if(Test-Path $ini){{ $c=Get-Content $ini -Raw; $c=$c -replace ';extension=pdo_sqlite','extension=pdo_sqlite'; $c=$c -replace ';extension=sqlite3','extension=sqlite3'; $c=$c -replace ';extension=pdo_mysql','extension=pdo_mysql'; $c=$c -replace ';extension=mbstring','extension=mbstring'; $c=$c -replace ';extension=openssl','extension=openssl'; $c=$c -replace ';extension=curl','extension=curl'; $c=$c -replace ';extension=gd','extension=gd'; $c=$c -replace ';extension=fileinfo','extension=fileinfo'; $c=$c -replace ';extension_dir = \""ext\""','extension_dir = \""ext\""'; Set-Content $ini $c; }}"""; Flags: runhidden; StatusMsg: "Enabling PHP extensions..."

; Launch after install
Filename: "{app}\start.bat"; Description: "Launch Grovixo IIMS now"; Flags: postinstall nowait shellexec

[UninstallRun]
Filename: "{app}\stop.bat"; Flags: runhidden

[UninstallDelete]
Type: filesandordirs; Name: "{app}\data"
Type: filesandordirs; Name: "{app}\app\database\database.sqlite"

[Messages]
WelcomeLabel1=Welcome to Grovixo IIMS Setup
WelcomeLabel2=This will install Grovixo Invoice & Inventory Management System v2.0 on your computer.%n%nNo XAMPP, MySQL, or technical setup required. Everything runs locally on your PC.%n%nClick Next to continue.
FinishedHeadingLabel=Grovixo IIMS Installed Successfully!
FinishedLabel=Grovixo has been installed on your computer.%n%nDouble-click the desktop icon "Grovixo IIMS" to start.%n%nDefault Login:%n  Email: hasmukhkikod@gmail.com%n  Password: admin123
