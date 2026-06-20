<?php
/**
5:  * Invoice & Inventory Management System (IIMS)
6:  * Database Migration: Return Tables
7:  */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

echo "=== IIMS RETURNS DATABASE MIGRATION ===\n";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    $driver = $conn->getAttribute(PDO::ATTR_DRIVER_NAME);
    
    echo "Active Driver: " . strtoupper($driver) . "\n";
    
    $queries = [];
    
    if ($driver === 'sqlite') {
        // SQLite Return Tables
        $queries[] = "
            CREATE TABLE IF NOT EXISTS sales_returns (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                invoice_id INTEGER NOT NULL,
                customer_id INTEGER NULL,
                return_no TEXT NOT NULL UNIQUE,
                return_date TEXT NOT NULL,
                total_amount REAL NOT NULL DEFAULT 0.00,
                remarks TEXT NULL,
                status TEXT DEFAULT 'ACTIVE',
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
                created_by INTEGER NULL,
                deleted_at TEXT NULL DEFAULT NULL,
                FOREIGN KEY (invoice_id) REFERENCES invoices(id),
                FOREIGN KEY (customer_id) REFERENCES customers(id)
            );
        ";
        
        $queries[] = "
            CREATE TABLE IF NOT EXISTS sales_return_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                sales_return_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                quantity REAL NOT NULL,
                rate REAL NOT NULL,
                amount REAL NOT NULL,
                status TEXT DEFAULT 'ACTIVE',
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
                created_by INTEGER NULL,
                deleted_at TEXT NULL DEFAULT NULL,
                FOREIGN KEY (sales_return_id) REFERENCES sales_returns(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id)
            );
        ";
        
        $queries[] = "
            CREATE TABLE IF NOT EXISTS purchase_returns (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                purchase_id INTEGER NOT NULL,
                supplier_id INTEGER NULL,
                return_no TEXT NOT NULL UNIQUE,
                return_date TEXT NOT NULL,
                total_amount REAL NOT NULL DEFAULT 0.00,
                remarks TEXT NULL,
                status TEXT DEFAULT 'ACTIVE',
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
                created_by INTEGER NULL,
                deleted_at TEXT NULL DEFAULT NULL,
                FOREIGN KEY (purchase_id) REFERENCES purchases(id),
                FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
            );
        ";
        
        $queries[] = "
            CREATE TABLE IF NOT EXISTS purchase_return_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                purchase_return_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                quantity REAL NOT NULL,
                cost_price REAL NOT NULL,
                amount REAL NOT NULL,
                status TEXT DEFAULT 'ACTIVE',
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
                created_by INTEGER NULL,
                deleted_at TEXT NULL DEFAULT NULL,
                FOREIGN KEY (purchase_return_id) REFERENCES purchase_returns(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id)
            );
        ";
    } else {
        // MySQL Return Tables
        $queries[] = "
            CREATE TABLE IF NOT EXISTS sales_returns (
                id INT AUTO_INCREMENT PRIMARY KEY,
                invoice_id INT NOT NULL,
                customer_id INT NULL,
                return_no VARCHAR(50) NOT NULL UNIQUE,
                return_date DATE NOT NULL,
                total_amount DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
                remarks TEXT NULL,
                status VARCHAR(20) DEFAULT 'ACTIVE',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                created_by INT NULL,
                deleted_at TIMESTAMP NULL DEFAULT NULL,
                FOREIGN KEY (invoice_id) REFERENCES invoices(id),
                FOREIGN KEY (customer_id) REFERENCES customers(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        
        $queries[] = "
            CREATE TABLE IF NOT EXISTS sales_return_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                sales_return_id INT NOT NULL,
                product_id INT NOT NULL,
                quantity DECIMAL(15, 2) NOT NULL,
                rate DECIMAL(15, 2) NOT NULL,
                amount DECIMAL(15, 2) NOT NULL,
                status VARCHAR(20) DEFAULT 'ACTIVE',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                created_by INT NULL,
                deleted_at TIMESTAMP NULL DEFAULT NULL,
                FOREIGN KEY (sales_return_id) REFERENCES sales_returns(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        
        $queries[] = "
            CREATE TABLE IF NOT EXISTS purchase_returns (
                id INT AUTO_INCREMENT PRIMARY KEY,
                purchase_id INT NOT NULL,
                supplier_id INT NULL,
                return_no VARCHAR(50) NOT NULL UNIQUE,
                return_date DATE NOT NULL,
                total_amount DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
                remarks TEXT NULL,
                status VARCHAR(20) DEFAULT 'ACTIVE',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                created_by INT NULL,
                deleted_at TIMESTAMP NULL DEFAULT NULL,
                FOREIGN KEY (purchase_id) REFERENCES purchases(id),
                FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        
        $queries[] = "
            CREATE TABLE IF NOT EXISTS purchase_return_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                purchase_return_id INT NOT NULL,
                product_id INT NOT NULL,
                quantity DECIMAL(15, 2) NOT NULL,
                cost_price DECIMAL(15, 2) NOT NULL,
                amount DECIMAL(15, 2) NOT NULL,
                status VARCHAR(20) DEFAULT 'ACTIVE',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                created_by INT NULL,
                deleted_at TIMESTAMP NULL DEFAULT NULL,
                FOREIGN KEY (purchase_return_id) REFERENCES purchase_returns(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        
        // Indices for performance
        $queries[] = "CREATE INDEX IF NOT EXISTS idx_sales_returns_invoice_id ON sales_returns(invoice_id);";
        $queries[] = "CREATE INDEX IF NOT EXISTS idx_purchase_returns_purchase_id ON purchase_returns(purchase_id);";
    }
    
    foreach ($queries as $index => $q) {
        $conn->exec($q);
        echo "Query " . ($index + 1) . " Executed successfully.\n";
    }
    
    echo "Migration completed successfully!\n";
    
} catch (Exception $e) {
    die("Migration failed: " . $e->getMessage() . "\n");
}
