<?php
namespace App\Models;

class DataTableHelper {
    
    /**
     * Process DataTables server-side request
     * 
     * @param Database $db Database connection
     * @param array $request $_POST or $_GET request data from DataTables
     * @param string $select Select clause (e.g., 'p.*, c.name as cat_name')
     * @param string $from From clause including JOINs (e.g., 'products p LEFT JOIN categories c ON p.cat_id = c.id')
     * @param string $baseWhere Base WHERE conditions (e.g., "p.status = 'ACTIVE' AND p.org_id = ?")
     * @param array $baseParams Parameters for the base WHERE condition
     * @param array $searchColumns Array of columns to search against
     * @param array $orderColumns Array mapping column index to actual column name for sorting
     * @param string $defaultOrder Default ORDER BY clause if none provided (e.g., 'p.id DESC')
     * @return array Array ready to be json_encoded for DataTables
     */
    public static function process($db, $request, $select, $from, $baseWhere, $baseParams, $searchColumns, $orderColumns, $defaultOrder = '') {
        $draw = isset($request['draw']) ? (int)$request['draw'] : 1;
        $start = isset($request['start']) ? (int)$request['start'] : 0;
        $length = isset($request['length']) ? (int)$request['length'] : 10;
        $searchValue = isset($request['search']['value']) ? trim($request['search']['value']) : '';
        
        $whereSql = $baseWhere;
        $params = $baseParams;
        
        // 1. Search Logic
        if (!empty($searchValue) && !empty($searchColumns)) {
            $searchParts = [];
            foreach ($searchColumns as $col) {
                $searchParts[] = "$col LIKE ?";
                $params[] = "%$searchValue%"; // Add param for each column
            }
            $searchSql = "(" . implode(" OR ", $searchParts) . ")";
            
            if (empty($whereSql)) {
                $whereSql = $searchSql;
            } else {
                $whereSql .= " AND " . $searchSql;
            }
        }
        
        $whereClause = empty($whereSql) ? "" : "WHERE $whereSql";
        
        // 2. Total records count (without search)
        $totalBaseWhere = empty($baseWhere) ? "" : "WHERE $baseWhere";
        $totalRecordsQuery = "SELECT COUNT(*) as count FROM $from $totalBaseWhere";
        $totalRecords = (int)$db->query($totalRecordsQuery, $baseParams)->fetch()['count'];
        
        // 3. Filtered records count (with search)
        if (!empty($searchValue) && !empty($searchColumns)) {
            $filteredRecordsQuery = "SELECT COUNT(*) as count FROM $from $whereClause";
            $filteredRecords = (int)$db->query($filteredRecordsQuery, $params)->fetch()['count'];
        } else {
            $filteredRecords = $totalRecords;
        }
        
        // 4. Order Logic
        $orderClause = $defaultOrder ? "ORDER BY $defaultOrder" : "";
        if (isset($request['order']) && is_array($request['order'])) {
            $orderArr = [];
            foreach ($request['order'] as $order) {
                $colIdx = (int)$order['column'];
                $dir = strtolower($order['dir']) === 'asc' ? 'ASC' : 'DESC';
                if (isset($orderColumns[$colIdx])) {
                    $orderArr[] = $orderColumns[$colIdx] . " " . $dir;
                }
            }
            if (!empty($orderArr)) {
                $orderClause = "ORDER BY " . implode(", ", $orderArr);
            }
        }
        
        // 5. Limit Logic
        $limitClause = "";
        if ($length != -1) {
            $limitClause = "LIMIT " . max(0, $length) . " OFFSET " . max(0, $start);
        }
        
        // 6. Final Query
        $finalQuery = "SELECT $select FROM $from $whereClause $orderClause $limitClause";
        $data = $db->query($finalQuery, $params)->fetchAll();
        
        return [
            "draw" => $draw,
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $filteredRecords,
            "data" => $data
        ];
    }
}
