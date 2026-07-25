<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/application/Models/Database.php';

echo "<pre>";

function createTablesForDb($dbName) {
    try {
        $db = new \App\Models\Database($dbName);
        $pdo = $db->getConnection();
        
        echo "Connected to: " . $dbName . "\n";
        
        $pdo->exec("
        CREATE TABLE IF NOT EXISTS `plans` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `plan_name` varchar(100) NOT NULL,
          `monthly_price` decimal(10,2) NOT NULL DEFAULT '0.00',
          `annual_price` decimal(10,2) NOT NULL DEFAULT '0.00',
          `features` text,
          `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        echo "- Created 'plans' table.\n";
        
        try {
            $pdo->exec("ALTER TABLE `plans` ADD COLUMN `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE'");
            echo "- Added 'status' column to plans table.\n";
        } catch (Exception $e) {
            // Ignore if column already exists
        }
        
        $pdo->exec("
        CREATE TABLE IF NOT EXISTS `organizations` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL,
          `email` varchar(150) NOT NULL,
          `phone` varchar(50) DEFAULT NULL,
          `address` text,
          `plan_id` int(11) DEFAULT NULL,
          `start_date` date DEFAULT NULL,
          `valid_until` date DEFAULT NULL,
          `status` enum('ACTIVE','INACTIVE','EXPIRED') DEFAULT 'ACTIVE',
          `is_verified` tinyint(1) DEFAULT '0',
          `verification_token` varchar(255) DEFAULT NULL,
          `is_approved` tinyint(1) DEFAULT '0',
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        echo "- Created 'organizations' table.\n";
        
        // Add default plan if missing
        $stmt = $pdo->query("SELECT COUNT(*) FROM plans");
        if ($stmt->fetchColumn() == 0) {
            $pdo->exec("INSERT INTO plans (plan_name, monthly_price, annual_price, features) VALUES ('Trial Plan', 0, 0, '15-day free trial')");
            echo "- Inserted default Trial Plan.\n";
        }
        
        echo "Success for " . $dbName . "!\n\n";
    } catch (Exception $e) {
        echo "Error for " . $dbName . ": " . $e->getMessage() . "\n\n";
    }
}

createTablesForDb('main');
createTablesForDb('demo');

echo "Finished.</pre>";
