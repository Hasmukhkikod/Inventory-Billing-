<?php
/**
 * Grovixo IIMS - Server Diagnostic Check
 * Upload this file to your Hostinger server and open it in browser
 * DELETE this file after setup is complete!
 */

echo "<html><head><title>Grovixo Setup Check</title>";
echo "<style>body{font-family:sans-serif;max-width:700px;margin:40px auto;padding:20px;background:#f5f5f5;}";
echo ".ok{color:#16a34a;}.fail{color:#dc2626;}.warn{color:#d97706;}";
echo ".box{background:#fff;padding:20px;border-radius:8px;margin:15px 0;box-shadow:0 1px 3px rgba(0,0,0,0.1);}";
echo "h1{color:#333;}</style></head><body>";
echo "<h1>Grovixo IIMS - Server Diagnostic</h1>";

$allOk = true;

// 1. PHP Version
echo "<div class='box'><h3>1. PHP Version</h3>";
$phpVer = phpversion();
if (version_compare($phpVer, '8.0', '>=')) {
    echo "<p class='ok'>✅ PHP $phpVer (OK - requires 8.0+)</p>";
} else {
    echo "<p class='fail'>❌ PHP $phpVer (REQUIRES 8.0+)</p>";
    $allOk = false;
}
echo "</div>";

// 2. Required Extensions
echo "<div class='box'><h3>2. PHP Extensions</h3>";
$required = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'session'];
foreach ($required as $ext) {
    if (extension_loaded($ext)) {
        echo "<p class='ok'>✅ $ext loaded</p>";
    } else {
        echo "<p class='fail'>❌ $ext NOT loaded</p>";
        $allOk = false;
    }
}
echo "</div>";

// 3. .env File
echo "<div class='box'><h3>3. Environment File (.env)</h3>";
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    echo "<p class='ok'>✅ .env file found</p>";
    $envContent = file_get_contents($envPath);

    // Parse active (non-commented) values
    $lines = explode("\n", $envContent);
    $envVars = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || $line[0] === '#') continue;
        if (strpos($line, '=') !== false) {
            list($key, $val) = explode('=', $line, 2);
            $envVars[trim($key)] = trim($val);
        }
    }

    $driver = $envVars['DB_DRIVER'] ?? 'not set';
    $host = $envVars['DB_HOST'] ?? 'not set';
    $dbname = $envVars['DB_NAME'] ?? 'not set';
    $user = $envVars['DB_USER'] ?? 'not set';
    $pass = isset($envVars['DB_PASS']) ? '****' : 'not set';

    echo "<p>DB_DRIVER = <b>$driver</b></p>";
    echo "<p>DB_HOST = <b>$host</b></p>";
    echo "<p>DB_NAME = <b>$dbname</b></p>";
    echo "<p>DB_USER = <b>$user</b></p>";
    echo "<p>DB_PASS = <b>$pass</b></p>";

    if ($driver !== 'mysql') {
        echo "<p class='warn'>⚠️ DB_DRIVER should be 'mysql' for Hostinger</p>";
    }
} else {
    echo "<p class='fail'>❌ .env file NOT found! This is the #1 cause of errors.</p>";
    echo "<p>Create a file named <b>.env</b> in the root directory with:</p>";
    echo "<pre style='background:#f0f0f0;padding:10px;border-radius:4px;'>";
    echo "DB_DRIVER=mysql\n";
    echo "DB_HOST=127.0.0.1\n";
    echo "DB_PORT=3306\n";
    echo "DB_NAME=u432404563_billing\n";
    echo "DB_USER=u432404563_billing\n";
    echo "DB_PASS=Grovixo@2030";
    echo "</pre>";
    $allOk = false;
}
echo "</div>";

// 4. Vendor/Autoload
echo "<div class='box'><h3>4. Composer Dependencies (vendor/)</h3>";
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    echo "<p class='ok'>✅ vendor/autoload.php found</p>";
} else {
    echo "<p class='fail'>❌ vendor/autoload.php NOT found! Upload the entire vendor/ folder.</p>";
    $allOk = false;
}
echo "</div>";

// 5. Database Connection
echo "<div class='box'><h3>5. Database Connection</h3>";
if (file_exists($envPath)) {
    try {
        // Load env manually for this test
        $lines = explode("\n", file_get_contents($envPath));
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || $line[0] === '#') continue;
            if (strpos($line, '=') !== false) {
                putenv($line);
            }
        }

        $dbDriver = getenv('DB_DRIVER') ?: 'mysql';
        $dbHost = getenv('DB_HOST') ?: '127.0.0.1';
        $dbPort = getenv('DB_PORT') ?: '3306';
        $dbName = getenv('DB_NAME') ?: 'invoices_systeam';
        $dbUser = getenv('DB_USER') ?: 'root';
        $dbPass = getenv('DB_PASS') ?: '';

        if ($dbDriver === 'mysql') {
            $dsn = "mysql:host=$dbHost;dbname=$dbName;port=$dbPort;charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "<p class='ok'>✅ MySQL connection successful!</p>";

            // Check tables
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            $tableCount = count($tables);

            if ($tableCount > 0) {
                echo "<p class='ok'>✅ Found $tableCount tables in database</p>";

                // Check if seed data exists
                try {
                    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
                    echo "<p class='ok'>✅ Users table has $userCount record(s)</p>";
                } catch (Exception $e) {
                    echo "<p class='warn'>⚠️ Could not query users table</p>";
                }
            } else {
                echo "<p class='fail'>❌ Database is EMPTY - no tables found!</p>";
                echo "<p>Go to Hostinger phpMyAdmin → Select your database → SQL tab → Paste contents of <b>full_install.sql</b> and click Go.</p>";
                $allOk = false;
            }
        } else {
            echo "<p class='warn'>⚠️ Driver is '$dbDriver' - for Hostinger use 'mysql'</p>";
        }
    } catch (PDOException $e) {
        echo "<p class='fail'>❌ Connection FAILED: " . htmlspecialchars($e->getMessage()) . "</p>";

        if (strpos($e->getMessage(), 'Unknown database') !== false) {
            echo "<p>The database doesn't exist yet. Create it in Hostinger panel first.</p>";
        } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
            echo "<p>Wrong username or password. Check your Hostinger MySQL credentials.</p>";
        } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
            echo "<p>Try changing DB_HOST to <b>localhost</b> instead of 127.0.0.1</p>";
        }
        $allOk = false;
    }
} else {
    echo "<p class='warn'>⚠️ Skipped - .env file not found</p>";
}
echo "</div>";

// 6. Key Directories
echo "<div class='box'><h3>6. Directory Permissions</h3>";
$dirs = ['uploads', 'backups', 'logs'];
foreach ($dirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (is_dir($path)) {
        if (is_writable($path)) {
            echo "<p class='ok'>✅ $dir/ exists and is writable</p>";
        } else {
            echo "<p class='fail'>❌ $dir/ exists but NOT writable (chmod 755)</p>";
            $allOk = false;
        }
    } else {
        echo "<p class='warn'>⚠️ $dir/ does not exist (will be auto-created)</p>";
    }
}
echo "</div>";

// 7. .htaccess
echo "<div class='box'><h3>7. Apache Rewrite</h3>";
if (file_exists(__DIR__ . '/.htaccess')) {
    echo "<p class='ok'>✅ .htaccess file found</p>";
} else {
    echo "<p class='fail'>❌ .htaccess file NOT found - URL routing won't work</p>";
    $allOk = false;
}
echo "</div>";

// Summary
echo "<div class='box' style='border-left:4px solid " . ($allOk ? '#16a34a' : '#dc2626') . ";'>";
if ($allOk) {
    echo "<h3 class='ok'>✅ All Checks Passed!</h3>";
    echo "<p>Your server is ready. Delete this <b>check.php</b> file and visit your site.</p>";
} else {
    echo "<h3 class='fail'>❌ Issues Found - Fix them above</h3>";
    echo "<p>Fix the issues marked with ❌ and refresh this page.</p>";
}
echo "</div>";

echo "<p style='color:#999;text-align:center;margin-top:30px;'>⚠️ Delete this file after setup is complete for security!</p>";
echo "</body></html>";
