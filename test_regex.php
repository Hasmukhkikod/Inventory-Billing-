<?php
$_SESSION['user']['org_id'] = 5;

function applyTenantScope(string $sql): string {
    $orgId = (int)$_SESSION['user']['org_id'];
    $globalTables = ['plans', 'organizations', 'permissions'];
    
    $type = strtoupper(strtok(trim($sql), " \n\t\r"));
    
    if ($type === 'SELECT') {
        if (preg_match('/FROM\s+([a-zA-Z0-9_]+)/i', $sql, $matches)) {
            $tableName = $matches[1];
            if (!in_array(strtolower($tableName), $globalTables)) {
                if (stripos($sql, 'WHERE') !== false) {
                    $sql = preg_replace('/WHERE/i', "WHERE $tableName.org_id = $orgId AND", $sql, 1);
                } else {
                    if (preg_match('/(GROUP BY|ORDER BY|LIMIT)/i', $sql, $m, PREG_OFFSET_CAPTURE)) {
                        $pos = $m[0][1];
                        $sql = substr_replace($sql, " WHERE $tableName.org_id = $orgId ", $pos, 0);
                    } else {
                        $sql .= " WHERE $tableName.org_id = $orgId";
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
                $vals = $matches[3];
                if (stripos($cols, 'org_id') === false) {
                    $newCols = $cols . ', org_id';
                    // We only want to replace the FIRST occurrence of VALUES (which is the main one).
                    // Actually regex is better.
                    $sql = preg_replace("/\($cols\)/", "($newCols)", $sql, 1);
                    $sql = preg_replace("/VALUES\s*\((.*?)\)/is", "VALUES ($1, $orgId)", $sql, 1);
                }
            }
        }
    }
    
    return $sql;
}

$queries = [
    "SELECT * FROM products",
    "SELECT * FROM products WHERE status = 'ACTIVE'",
    "SELECT * FROM products ORDER BY id DESC LIMIT 10",
    "SELECT p.*, c.name FROM products p JOIN categories c ON p.cat_id = c.id WHERE p.status = 'ACTIVE'",
    "UPDATE products SET status = 'INACTIVE' WHERE id = 5",
    "DELETE FROM products WHERE id = 5",
    "INSERT INTO products (name, sku, price) VALUES ('Test', 'SKU', 100)"
];

foreach ($queries as $q) {
    echo applyTenantScope($q) . "\n";
}
