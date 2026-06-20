<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Database Helper (PDO Class wrapper)
 */
namespace App\Models;

use PDO;
use PDOException;
use PDOStatement;
use Exception;

class Database {
    private ?PDO $pdo = null;
    private string $driver;

    public function __construct() {
        $this->driver = DB_DRIVER;
        
        try {
            if ($this->driver === 'sqlite') {
                $dbExists = file_exists(SQLITE_FILE);
                $this->pdo = new PDO("sqlite:" . SQLITE_FILE);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                
                // Enable SQLite Foreign Key support
                $this->pdo->exec("PRAGMA foreign_keys = ON;");
                
                // Automatically initialize database if sqlite file is new or tables don't exist
                if (!$dbExists || $this->isDatabaseEmpty()) {
                    $this->initializeDatabase();
                }
            } else {
                // MySQL PDO Connection
                // Attempt direct connection first
                try {
                    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";port=" . DB_PORT . ";charset=utf8mb4";
                    $this->pdo = new PDO($dsn, DB_USER, DB_PASS);
                } catch (PDOException $e) {
                    // Check if DB doesn't exist error (code 1049) and create it
                    if ($e->getCode() == 1049 || strpos($e->getMessage(), 'Unknown database') !== false) {
                        $dsnNoDb = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
                        $tempPdo = new PDO($dsnNoDb, DB_USER, DB_PASS);
                        $tempPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
                        $tempPdo = null; // Close connection
                        
                        // Retry original connection with DB Name
                        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";port=" . DB_PORT . ";charset=utf8mb4";
                        $this->pdo = new PDO($dsn, DB_USER, DB_PASS);
                    } else {
                        throw $e;
                    }
                }
                
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                
                if ($this->isDatabaseEmpty()) {
                    $this->initializeDatabase();
                }
            }
        } catch (PDOException $e) {
            die("Database Connection Error: " . $e->getMessage());
        }
    }

    public function getConnection(): PDO {
        return $this->pdo;
    }

    /**
     * Check if the database contains no tables (needs seeding)
     */
    private function isDatabaseEmpty(): bool {
        try {
            // Check if users table exists and contains records
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM users");
            $count = (int)$stmt->fetchColumn();
            return $count === 0;
        } catch (PDOException $e) {
            return true;
        }
    }

    /**
     * Run migrations and seeding automatically
     */
    public function initializeDatabase(): bool {
        $schemaFile = BASE_DIR . '/database/schema.sql';
        $seedFile = BASE_DIR . '/database/seed.sql';

        if (!file_exists($schemaFile)) {
            return false;
        }

        try {
            // Read schema SQL
            $schemaSql = file_get_contents($schemaFile);

            if ($this->driver === 'sqlite') {
                // Translate MySQL syntax into valid SQLite syntax
                $schemaSql = preg_replace('/INT\s+AUTO_INCREMENT\s+PRIMARY\s+KEY/i', 'INTEGER PRIMARY KEY AUTOINCREMENT', $schemaSql);
                $schemaSql = preg_replace('/INT\s+AUTO_INCREMENT/i', 'INTEGER', $schemaSql);
                
                // Replace general INT constraints with INTEGER for foreign keys mappings
                $schemaSql = preg_replace('/role_id INT/i', 'role_id INTEGER', $schemaSql);
                $schemaSql = preg_replace('/category_id INT/i', 'category_id INTEGER', $schemaSql);
                $schemaSql = preg_replace('/brand_id INT/i', 'brand_id INTEGER', $schemaSql);
                $schemaSql = preg_replace('/unit_id INT/i', 'unit_id INTEGER', $schemaSql);
                $schemaSql = preg_replace('/product_id INT/i', 'product_id INTEGER', $schemaSql);
                $schemaSql = preg_replace('/customer_id INT/i', 'customer_id INTEGER', $schemaSql);
                $schemaSql = preg_replace('/supplier_id INT/i', 'supplier_id INTEGER', $schemaSql);
                $schemaSql = preg_replace('/purchase_id INT/i', 'purchase_id INTEGER', $schemaSql);
                $schemaSql = preg_replace('/invoice_id INT/i', 'invoice_id INTEGER', $schemaSql);
                $schemaSql = preg_replace('/user_id INT/i', 'user_id INTEGER', $schemaSql);
                $schemaSql = preg_replace('/created_by INT/i', 'created_by INTEGER', $schemaSql);
                $schemaSql = preg_replace('/permission_id INT/i', 'permission_id INTEGER', $schemaSql);
                $schemaSql = preg_replace('/id INT/i', 'id INTEGER', $schemaSql);
            }

            $this->executeMultiQuery($schemaSql);

            // Seed if seed file exists
            if (file_exists($seedFile)) {
                $seedSql = file_get_contents($seedFile);
                if ($this->driver === 'sqlite') {
                    $seedSql = preg_replace('/INSERT\s+IGNORE\s+INTO/i', 'INSERT OR IGNORE INTO', $seedSql);
                }
                $this->executeMultiQuery($seedSql);
            }

            return true;
        } catch (Exception $e) {
            error_log("Database initialization failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Execute SQL string containing multiple queries separated by semicolons
     */
    private function executeMultiQuery(string $sql): void {
        // Strip comments
        $sql = preg_replace('/--.*\n/', '', $sql);
        // Split queries by semicolon
        $queries = explode(';', $sql);
        
        foreach ($queries as $query) {
            $trimmed = trim($query);
            if (!empty($trimmed)) {
                $this->pdo->exec($trimmed);
            }
        }
    }

    /**
     * Helper for prepared statement execution
     */
    public function query(string $sql, array $params = []): PDOStatement {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Query Error: " . $e->getMessage() . " (SQL: $sql)");
        }
    }

    /**
     * Helper to insert a row and return last insert ID
     */
    public function insert(string $sql, array $params = []): string {
        $this->query($sql, $params);
        return $this->pdo->lastInsertId();
    }

    /**
     * Helper to perform transactional actions
     */
    public function transaction(callable $callback) {
        $this->pdo->beginTransaction();
        try {
            $result = $callback($this);
            $this->pdo->commit();
            return $result;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
