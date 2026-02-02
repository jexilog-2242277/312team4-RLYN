<?php
session_start();
header('Content-Type: application/json');
require_once "../includes/db_connect.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "Not logged in"]);
    exit;
}

$userRole = $_SESSION['role'] ?? 'student';
$orgId = $_SESSION['org_id'] ?? null;

try {
    // Only fetch items where status is 'returned'
    // For students, filter by their specific org_id
    $query = "SELECT a.activity_id, a.name, a.academic_year, a.sdg_relation, a.return_reason, 
              (SELECT COUNT(*) FROM documents d WHERE d.activity_id = a.activity_id) as doc_count
              FROM activities a 
              WHERE a.status = 'returned'";
    
    $params = [];
    if ($userRole === 'student' && $orgId) {
        $query .= " AND a.org_id = $1";
        $params[] = $orgId;
    }

    $result = pg_query_params($conn, $query, $params);
    $items = pg_fetch_all($result) ?: [];

    echo json_encode(["success" => true, "items" => $items]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>