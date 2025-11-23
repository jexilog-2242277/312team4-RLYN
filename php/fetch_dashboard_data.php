<?php
session_start();
header('Content-Type: application/json');
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
  echo json_encode(["error" => "Not logged in"]);
  exit;
}

$userRole = $_SESSION['role'] ?? 'guest';
$orgId = $_SESSION['org_id'] ?? null;

try {
    if ($userRole === 'osas' || $userRole === 'admin') {
        $query = "SELECT a.*, o.name AS org_name,
                  (SELECT COUNT(*) FROM documents d WHERE d.activity_id = a.activity_id) AS doc_count
                  FROM activities a
                  JOIN organizations o ON a.org_id = o.org_id
                  ORDER BY a.created_at DESC";
        $result = pg_query($conn, $query);
    } else if ($orgId) {
        $query = "SELECT a.*, o.name AS org_name,
                  (SELECT COUNT(*) FROM documents d WHERE d.activity_id = a.activity_id) AS doc_count
                  FROM activities a
                  JOIN organizations o ON a.org_id = o.org_id
                  WHERE a.org_id = $1
                  ORDER BY a.created_at DESC";
        $result = pg_query_params($conn, $query, [$orgId]);
    } else {
        echo json_encode(["error" => "No organization ID found"]);
        exit;
    }

    $activities = pg_fetch_all($result) ?: [];

    // Normalize doc_count to int if present
    foreach ($activities as &$act) {
        if (isset($act['doc_count'])) {
            $act['doc_count'] = (int)$act['doc_count'];
        } else {
            $act['doc_count'] = 0;
        }
    }
    unset($act);

    // Get documents list (recent) — either all (admin) or for the org
    if ($userRole === 'osas' || $userRole === 'admin') {
        $docQuery = "SELECT d.*, o.name AS org_name, a.name AS activity_name
                     FROM documents d
                     LEFT JOIN organizations o ON d.org_id = o.org_id
                     LEFT JOIN activities a ON d.activity_id = a.activity_id
                     ORDER BY d.uploaded_at DESC
                     LIMIT 50";
        $docResult = pg_query($conn, $docQuery);
    } else {
        $docQuery = "SELECT d.*, o.name AS org_name, a.name AS activity_name
                     FROM documents d
                     LEFT JOIN organizations o ON d.org_id = o.org_id
                     LEFT JOIN activities a ON d.activity_id = a.activity_id
                     WHERE d.org_id = $1
                     ORDER BY d.uploaded_at DESC
                     LIMIT 50";
        $docResult = pg_query_params($conn, $docQuery, [$orgId]);
    }

    $documents = pg_fetch_all($docResult) ?: [];

    // Count documents
    if ($userRole === 'osas' || $userRole === 'admin') {
        $docCountRes = pg_query($conn, "SELECT COUNT(*) FROM documents");
    } else {
        $docCountRes = pg_query_params($conn, "SELECT COUNT(*) FROM documents WHERE org_id = $1", [$orgId]);
    }
    $totalDocs = (int)pg_fetch_result($docCountRes, 0, 0);

    echo json_encode([
        "activities" => $activities,
        "documents" => $documents,
        "totalActivities" => count($activities),
        "totalDocuments" => $totalDocs
    ]);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
