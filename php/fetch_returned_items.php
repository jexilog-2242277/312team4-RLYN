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
    $items = [];

    // --- Fetch returned activities ---
    $activityQuery = "SELECT a.activity_id as id, a.name, a.description, a.academic_year, a.sdg_relation, a.return_reason,
                             (SELECT COUNT(*) FROM documents d WHERE d.activity_id = a.activity_id) as doc_count
                      FROM activities a
                      WHERE a.status = 'returned'";
    
    $activityParams = [];
    if ($userRole === 'student' && $orgId) {
        $activityQuery .= " AND a.org_id = $1";
        $activityParams[] = $orgId;
    }

    $activityResult = pg_query_params($conn, $activityQuery, $activityParams);
    while ($row = pg_fetch_assoc($activityResult)) {
        $row['type'] = 'activity';
        $items[] = $row;
    }

    // --- Fetch returned documents ---
    $docQuery = "SELECT d.document_id as id, d.doc_name as name, d.file_path, d.return_reason
                 FROM documents d
                 WHERE d.status = 'returned'";
    
    $docParams = [];
    if ($userRole === 'student' && $orgId) {
        $docQuery .= " AND d.org_id = $1";
        $docParams[] = $orgId;
    }

    $docResult = pg_query_params($conn, $docQuery, $docParams);
    while ($row = pg_fetch_assoc($docResult)) {
        $row['type'] = 'document';
        $items[] = $row;
    }

    echo json_encode(["success" => true, "items" => $items]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
