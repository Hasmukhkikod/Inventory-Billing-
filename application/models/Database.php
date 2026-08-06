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

    public function __construct(string $connection = 'core') {
        $this->driver = DB_DRIVER;
        
        // Auto-switch to demo if session is flagged
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['is_demo']) && $_SESSION['is_demo'] === true) {
            $connection = 'demo';
        }
        
        $dbHost = ($connection === 'demo') ? ($_ENV['DEMO_DB_HOST'] ?? DB_HOST) : DB_HOST;
        $dbName = ($connection === 'demo') ? ($_ENV['DEMO_DB_NAME'] ?? 'demo_billing') : DB_NAME;
        $dbUser = ($connection === 'demo') ? ($_ENV['DEMO_DB_USER'] ?? DB_USER) : DB_USER;
        $dbPass = ($connection === 'demo') ? ($_ENV['DEMO_DB_PASS'] ?? DB_PASS) : DB_PASS;
        
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
                try {
                    $dsn = "mysql:host=" . $dbHost . ";dbname=" . $dbName . ";port=" . DB_PORT . ";charset=utf8mb4";
                    $this->pdo = new PDO($dsn, $dbUser, $dbPass);
                } catch (PDOException $e) {
                    if ($e->getCode() == 1049 || strpos($e->getMessage(), 'Unknown database') !== false) {
                        // Try to create DB (works on local dev, not on shared hosting)
                        try {
                            $dsnNoDb = "mysql:host=" . $dbHost . ";port=" . DB_PORT . ";charset=utf8mb4";
                            $tempPdo = new PDO($dsnNoDb, $dbUser, $dbPass);
                            $tempPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                            $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `" . $dbName . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
                            $tempPdo = null;

                            $dsn = "mysql:host=" . $dbHost . ";dbname=" . $dbName . ";port=" . DB_PORT . ";charset=utf8mb4";
                            $this->pdo = new PDO($dsn, $dbUser, $dbPass);
                        } catch (PDOException $createErr) {
                            throw new PDOException(
                                "Database '" . $dbName . "' does not exist and could not be auto-created. " .
                                "Please create the database manually via your hosting panel and import full_install.sql. " .
                                "Original error: " . $e->getMessage()
                            );
                        }
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
                // Translate MySQL → SQLite syntax
                $schemaSql = preg_replace('/INT\s+AUTO_INCREMENT\s+PRIMARY\s+KEY/i', 'INTEGER PRIMARY KEY AUTOINCREMENT', $schemaSql);
                $schemaSql = preg_replace('/INT\s+AUTO_INCREMENT/i', 'INTEGER', $schemaSql);
                // Universal: any column ending with _id INT or named id INT → INTEGER
                $schemaSql = preg_replace('/(\w+)\s+INT\b(?!\s*AUTO_INCREMENT)/i', '$1 INTEGER', $schemaSql);
                // Remove MySQL-specific clauses
                $schemaSql = preg_replace('/\s*ENGINE\s*=\s*InnoDB[^;]*/i', '', $schemaSql);
                $schemaSql = preg_replace('/\s*DEFAULT\s+CHARSET\s*=\s*\w+/i', '', $schemaSql);
                $schemaSql = preg_replace('/\s*ON\s+UPDATE\s+CURRENT_TIMESTAMP/i', '', $schemaSql);
                // Remove CREATE INDEX statements (SQLite handles these differently)
                $schemaSql = preg_replace('/CREATE\s+INDEX\s+[^;]+;/i', '', $schemaSql);
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

    private function applyTenantScope(string $sql): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['org_id']) || $_SESSION['org_id'] == 0) {
            return $sql;
        }

        $orgId = (int)$_SESSION['org_id'];
        $globalTables = ['plans', 'organizations', 'permissions', 'system_announcements'];
        
        $type = strtoupper(strtok(trim($sql), " \n\t\r"));
        
        if ($type === 'SELECT') {
            if (preg_match('/FROM\s+([a-zA-Z0-9_]+)(?:\s+(AS\s+)?([a-zA-Z0-9_]+))?/i', $sql, $matches)) {
                $tableName = $matches[1];
                $alias = (isset($matches[3]) && !in_array(strtoupper($matches[3]), ['ON', 'WHERE', 'JOIN', 'ORDER', 'GROUP', 'LIMIT', 'LEFT', 'RIGHT', 'INNER'])) ? $matches[3] : $tableName;
                
                if (!in_array(strtolower($tableName), $globalTables)) {
                    if (stripos($sql, 'WHERE') !== false) {
                        $sql = preg_replace('/WHERE/i', "WHERE $alias.org_id = $orgId AND", $sql, 1);
                    } else {
                        if (preg_match('/(GROUP BY|ORDER BY|LIMIT)/i', $sql, $m, PREG_OFFSET_CAPTURE)) {
                            $pos = $m[0][1];
                            $sql = substr_replace($sql, " WHERE $alias.org_id = $orgId ", $pos, 0);
                        } else {
                            $sql .= " WHERE $alias.org_id = $orgId";
                        }
                    }
                }
            }
        } elseif ($type === 'UPDATE') {
             if (preg_match('/UPDATE\s+([a-zA-Z0-9_]+)/i', $sql, $matches)) {
                 $tableName = $matches[1];
                 if (!in_array(strtolower($tableName), $globalTables)) {
                     if (stripos($sql, 'WHERE') !== false) {
                        $sql = preg_replace('/WHERE/i', "WHERE org_id = $orgId AND", $sql, 1);
                     } else {
                        $sql .= " WHERE org_id = $orgId";
                     }
                 }
             }
        } elseif ($type === 'DELETE') {
             if (preg_match('/FROM\s+([a-zA-Z0-9_]+)/i', $sql, $matches)) {
                 $tableName = $matches[1];
                 if (!in_array(strtolower($tableName), $globalTables)) {
                     if (stripos($sql, 'WHERE') !== false) {
                        $sql = preg_replace('/WHERE/i', "WHERE org_id = $orgId AND", $sql, 1);
                     } else {
                        $sql .= " WHERE org_id = $orgId";
                     }
                 }
             }
        } elseif ($type === 'INSERT') {
            if (preg_match('/INSERT\s+(?:IGNORE\s+)?INTO\s+([a-zA-Z0-9_]+)\s*\((.*?)\)\s*VALUES\s*\((.*?)\)/is', $sql, $matches)) {
                $tableName = $matches[1];
                if (!in_array(strtolower($tableName), $globalTables)) {
                    $cols = $matches[2];
                    if (stripos($cols, 'org_id') === false) {
                        $newCols = $cols . ', org_id';
                        $sql = preg_replace("/\(".preg_quote($cols, '/')."\)/", "($newCols)", $sql, 1);
                        $sql = preg_replace("/VALUES\s*\((.*?)\)/is", "VALUES ($1, $orgId)", $sql, 1);
                    }
                }
            }
        }
        
        return $sql;
    }

    /**
     * Helper for prepared statement execution
     */
    public function query(string $sql, array $params = []): PDOStatement {
        $sql = $this->applyTenantScope($sql);
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
