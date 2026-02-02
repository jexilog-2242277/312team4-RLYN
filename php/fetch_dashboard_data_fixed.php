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

// Read returns_log to get returned item IDs
$returnedActivityIds = [];
$returnedDocumentIds = [];
$returnLogsDir = __DIR__ . '/../returns_log';
if (is_dir($returnLogsDir)) {
    $rFiles = glob($returnLogsDir . '/return_*.json');
    foreach ($rFiles as $rf) {
        $c = json_decode(file_get_contents($rf), true);
        if (is_array($c) && isset($c['type']) && isset($c['id']) && isset($c['status']) && $c['status'] === 'returned') {
            if ($c['type'] === 'activity') {
                $returnedActivityIds[] = (int)$c['id'];
            } elseif ($c['type'] === 'document') {
                $returnedDocumentIds[] = (int)$c['id'];
            }
        }
    }
}

// Get filters
$searchTerm = $_GET['search'] ?? '';
$yearFilter = $_GET['year'] ?? '';
$sdgFilter = $_GET['sdgs'] ?? '';

$activities = [];
$documents = [];

try {
    // ===== ACTIVITIES =====
    if ($userRole === 'osas' || $userRole === 'admin') {
        // OSAs/Admins: see all activities EXCEPT returned ones
        $params = [];
        $query = "SELECT a.*, o.name AS org_name,
                  (SELECT COUNT(*) FROM documents d WHERE d.activity_id = a.activity_id) AS doc_count
                  FROM activities a
                  JOIN organizations o ON a.org_id = o.org_id
                  WHERE 1=1";
        
        // Add search filter
        if ($searchTerm !== '') {
            $query .= " AND (a.name ILIKE $1 OR a.sdg_relation ILIKE $1 OR a.academic_year ILIKE $1)";
            $params[] = '%' . $searchTerm . '%';
        }
        
        // Add year filter
        if ($yearFilter !== '') {
            $idx = count($params) + 1;
            $query .= " AND a.academic_year = $$idx";
            $params[] = $yearFilter;
        }
        
        // Add SDG filter
        if ($sdgFilter !== '') {
            $sdgs = explode(',', $sdgFilter);
            $sdgClauses = [];
            foreach ($sdgs as $sdg) {
                $idx = count($params) + 1;
                $sdgClauses[] = "a.sdg_relation ~* $$idx";
                $params[] = '\ySDG ' . trim($sdg) . '\y';
            }
            if (!empty($sdgClauses)) {
                $query .= " AND (" . implode(' OR ', $sdgClauses) . ")";
            }
        }
        
        // EXCLUDE returned activities
        if (!empty($returnedActivityIds)) {
            $query .= " AND a.activity_id NOT IN (" . implode(',', $returnedActivityIds) . ")";
        }
        
        $query .= " ORDER BY a.created_at DESC";
        
        $result = pg_query_params($conn, $query, $params);
        if (!$result) {
            throw new Exception("Activity query failed: " . pg_last_error($conn));
        }
        $activities = pg_fetch_all($result) ?: [];
        
    } else {
        // Org members: see all activities for their org, including returned ones
        if (!$orgId) {
            throw new Exception("No organization ID found");
        }
        
        $params = [$orgId];
        $query = "SELECT a.*, o.name AS org_name,
                  (SELECT COUNT(*) FROM documents d WHERE d.activity_id = a.activity_id) AS doc_count
                  FROM activities a
                  JOIN organizations o ON a.org_id = o.org_id
                  WHERE a.org_id = $1";
        
        // Add search filter
        if ($searchTerm !== '') {
            $query .= " AND (a.name ILIKE $2 OR a.sdg_relation ILIKE $2 OR a.academic_year ILIKE $2)";
            $params[] = '%' . $searchTerm . '%';
        }
        
        // Add year filter
        if ($yearFilter !== '') {
            $idx = count($params) + 1;
            $query .= " AND a.academic_year = $$idx";
            $params[] = $yearFilter;
        }
        
        // Add SDG filter
        if ($sdgFilter !== '') {
            $sdgs = explode(',', $sdgFilter);
            $sdgClauses = [];
            foreach ($sdgs as $sdg) {
                $idx = count($params) + 1;
                $sdgClauses[] = "a.sdg_relation ~* $$idx";
                $params[] = '\ySDG ' . trim($sdg) . '\y';
            }
            if (!empty($sdgClauses)) {
                $query .= " AND (" . implode(' OR ', $sdgClauses) . ")";
            }
        }
        
        $query .= " ORDER BY a.created_at DESC";
        
        $result = pg_query_params($conn, $query, $params);
        if (!$result) {
            throw new Exception("Activity query failed: " . pg_last_error($conn));
        }
        $activities = pg_fetch_all($result) ?: [];
    }
    
    // Mark returned activities
    foreach ($activities as &$act) {
        $act['doc_count'] = (int)($act['doc_count'] ?? 0);
        $act['is_returned'] = in_array((int)$act['activity_id'], $returnedActivityIds);
    }
    
    // ===== DOCUMENTS =====
    if ($userRole === 'osas' || $userRole === 'admin') {
        // OSAs/Admins: see all documents EXCEPT returned ones
        $params = [];
        $query = "SELECT d.*, o.name AS org_name, a.name AS activity_name
                  FROM documents d
                  LEFT JOIN organizations o ON d.org_id = o.org_id
                  JOIN activities a ON d.activity_id = a.activity_id
                  WHERE 1=1";
        
        // Add search filter
        if ($searchTerm !== '') {
            $query .= " AND (a.name ILIKE $1 OR d.document_name ILIKE $1 OR d.document_type ILIKE $1)";
            $params[] = '%' . $searchTerm . '%';
        }
        
        // Add year filter
        if ($yearFilter !== '') {
            $idx = count($params) + 1;
            $query .= " AND a.academic_year = $$idx";
            $params[] = $yearFilter;
        }
        
        // Add SDG filter
        if ($sdgFilter !== '') {
            $sdgs = explode(',', $sdgFilter);
            $sdgClauses = [];
            foreach ($sdgs as $sdg) {
                $idx = count($params) + 1;
                $sdgClauses[] = "a.sdg_relation ~* $$idx";
                $params[] = '\ySDG ' . trim($sdg) . '\y';
            }
            if (!empty($sdgClauses)) {
                $query .= " AND (" . implode(' OR ', $sdgClauses) . ")";
            }
        }
        
        // EXCLUDE returned documents
        if (!empty($returnedDocumentIds)) {
            $query .= " AND d.document_id NOT IN (" . implode(',', $returnedDocumentIds) . ")";
        }
        
        $query .= " ORDER BY d.uploaded_at DESC LIMIT 50";
        
        $result = pg_query_params($conn, $query, $params);
        if (!$result) {
            throw new Exception("Document query failed: " . pg_last_error($conn));
        }
        $documents = pg_fetch_all($result) ?: [];
        
    } else {
        // Org members: see all documents for their org, including returned ones
        if (!$orgId) {
            throw new Exception("No organization ID found");
        }
        
        $params = [$orgId];
        $query = "SELECT d.*, o.name AS org_name, a.name AS activity_name
                  FROM documents d
                  LEFT JOIN organizations o ON d.org_id = o.org_id
                  JOIN activities a ON d.activity_id = a.activity_id
                  WHERE d.org_id = $1";
        
        // Add search filter
        if ($searchTerm !== '') {
            $query .= " AND (a.name ILIKE $2 OR d.document_name ILIKE $2 OR d.document_type ILIKE $2)";
            $params[] = '%' . $searchTerm . '%';
        }
        
        // Add year filter
        if ($yearFilter !== '') {
            $idx = count($params) + 1;
            $query .= " AND a.academic_year = $$idx";
            $params[] = $yearFilter;
        }
        
        // Add SDG filter
        if ($sdgFilter !== '') {
            $sdgs = explode(',', $sdgFilter);
            $sdgClauses = [];
            foreach ($sdgs as $sdg) {
                $idx = count($params) + 1;
                $sdgClauses[] = "a.sdg_relation ~* $$idx";
                $params[] = '\ySDG ' . trim($sdg) . '\y';
            }
            if (!empty($sdgClauses)) {
                $query .= " AND (" . implode(' OR ', $sdgClauses) . ")";
            }
        }
        
        $query .= " ORDER BY d.uploaded_at DESC LIMIT 50";
        
        $result = pg_query_params($conn, $query, $params);
        if (!$result) {
            throw new Exception("Document query failed: " . pg_last_error($conn));
        }
        $documents = pg_fetch_all($result) ?: [];
    }
    
    // Mark returned documents
    foreach ($documents as &$doc) {
        $doc['is_returned'] = in_array((int)$doc['document_id'], $returnedDocumentIds);
    }
    
    // Total counts
    $totalActivities = count($activities);
    $totalDocuments = count($documents);

    echo json_encode([
        "userRole" => $userRole,
        "activities" => $activities,
        "documents" => $documents,
        "totalActivities" => $totalActivities,
        "totalDocuments" => $totalDocuments
    ]);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
