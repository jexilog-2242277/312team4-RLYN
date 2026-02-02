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

// Capture Filters from GET request
$searchTerm = $_GET['search'] ?? '';
$yearFilter = $_GET['year'] ?? '';
$sdgFilter = $_GET['sdgs'] ?? ''; // Received as a comma-separated string, e.g., "1,4,8"

try {
    $activitySearchFilter = '';
    $documentSearchFilter = ''; 
    $params = [];
    $paramIndex = 1;

    // --- 1. Admin/OSAS: Handle Global Filters first ---
    if ($userRole === 'osas' || $userRole === 'admin') {
        
        // Search Term Logic (Broad matching)
        if ($searchTerm !== '') {
            $searchWord = '%' . $searchTerm . '%'; 
            $activitySearchFilter .= " AND (a.name ILIKE $" . $paramIndex . " OR a.sdg_relation ILIKE $" . $paramIndex . " OR a.academic_year ILIKE $" . $paramIndex . ")";
            $documentSearchFilter .= " AND (a.name ILIKE $" . $paramIndex . " OR a.sdg_relation ILIKE $" . $paramIndex . " OR a.academic_year ILIKE $" . $paramIndex . " OR d.document_name ILIKE $" . $paramIndex . " OR d.document_type ILIKE $" . $paramIndex . ")";
            $params[] = $searchWord;
            $paramIndex++;
        }

        // Academic Year Filter
        if ($yearFilter !== '') {
            $activitySearchFilter .= " AND a.academic_year = $" . $paramIndex;
            $documentSearchFilter .= " AND a.academic_year = $" . $paramIndex;
            $params[] = $yearFilter;
            $paramIndex++;
        }

        // SDG Filter (Multi-select)
        if ($sdgFilter !== '') {
            $sdgs = explode(',', $sdgFilter);
            $sdgClauses = [];
            foreach ($sdgs as $sdg) {
                $sdgClauses[] = "a.sdg_relation ~* $" . $paramIndex;
                $params[] = '\ySDG ' . trim($sdg) . '\y';
                $paramIndex++;
            }
            $activitySearchFilter .= " AND (" . implode(' OR ', $sdgClauses) . ")";
            $documentSearchFilter .= " AND (" . implode(' OR ', $sdgClauses) . ")";
        }

        // OSAS Side Activity Query: Exclude returned status
        $actQuery = "SELECT a.*, o.name AS org_name,
            (SELECT COUNT(*) FROM documents d WHERE d.activity_id = a.activity_id) AS doc_count
            FROM activities a
            JOIN organizations o ON a.org_id = o.org_id
            WHERE a.status != 'returned' " . $activitySearchFilter . "
            ORDER BY a.created_at DESC";

        $result = pg_query_params($conn, $actQuery, $params);

    } 
    // --- 2. Organization User (Student): Organization-Specific Filters ---
    else if ($userRole === 'student' && $orgId) {
        $params[] = $orgId; // $1 is always Org ID
        $paramIndex = 2;

        if ($searchTerm !== '') {
            $activitySearchFilter .= " AND (a.name ILIKE $" . $paramIndex . " OR a.sdg_relation ILIKE $" . $paramIndex . " OR a.academic_year ILIKE $" . $paramIndex . ")";
            $documentSearchFilter .= " AND (a.name ILIKE $" . $paramIndex . " OR a.sdg_relation ILIKE $" . $paramIndex . " OR a.academic_year ILIKE $" . $paramIndex . " OR d.document_name ILIKE $" . $paramIndex . " OR d.document_type ILIKE $" . $paramIndex . ")";
            $params[] = '%' . $searchTerm . '%';
            $paramIndex++;
        }

        if ($yearFilter !== '') {
            $activitySearchFilter .= " AND a.academic_year = $" . $paramIndex;
            $documentSearchFilter .= " AND a.academic_year = $" . $paramIndex;
            $params[] = $yearFilter;
            $paramIndex++;
        }

        // SDG Filter (Multi-select)
        if ($sdgFilter !== '') {
            $sdgs = explode(',', $sdgFilter);
            $sdgClauses = [];
            foreach ($sdgs as $sdg) {
                $sdgClauses[] = "a.sdg_relation ~* $" . $paramIndex;
                $params[] = '\ySDG ' . trim($sdg) . '\y';
                $paramIndex++;
            }
            $activitySearchFilter .= " AND (" . implode(' OR ', $sdgClauses) . ")";
            $documentSearchFilter .= " AND (" . implode(' OR ', $sdgClauses) . ")";
        }

        // Student Side Activity Query: Exclude returned status for their org
        $actQuery = "SELECT a.*, o.name AS org_name,
            (SELECT COUNT(*) FROM documents d WHERE d.activity_id = a.activity_id) AS doc_count
            FROM activities a
            JOIN organizations o ON a.org_id = o.org_id
            WHERE a.org_id = $1 
            AND a.status != 'returned' " . $activitySearchFilter . "
            ORDER BY a.created_at DESC";

        $result = pg_query_params($conn, $actQuery, $params);
    } else {
        echo json_encode(["error" => "No organization ID found"]);
        exit;
    }

    $activities = pg_fetch_all($result) ?: [];
    foreach ($activities as &$act) {
        $act['doc_count'] = isset($act['doc_count']) ? (int)$act['doc_count'] : 0;
    }

    // --- 3. Document Query Execution ---
    if ($userRole === 'osas' || $userRole === 'admin') {
        // OSAS Side Document Query: Exclude returned documents
        $docQuery = "SELECT d.*, o.name AS org_name, a.name AS activity_name
                     FROM documents d
                     LEFT JOIN organizations o ON d.org_id = o.org_id
                     JOIN activities a ON d.activity_id = a.activity_id
                     WHERE d.status != 'returned' " . $documentSearchFilter . " 
                     ORDER BY d.uploaded_at DESC LIMIT 50";
        $docResult = pg_query_params($conn, $docQuery, $params);
    } else {
        // Student Side Document Query: Exclude returned documents for their org
        $docQuery = "SELECT d.*, o.name AS org_name, a.name AS activity_name
                     FROM documents d
                     LEFT JOIN organizations o ON d.org_id = o.org_id
                     JOIN activities a ON d.activity_id = a.activity_id
                     WHERE d.org_id = $1 
                     AND d.status != 'returned' " . $documentSearchFilter . " 
                     ORDER BY d.uploaded_at DESC LIMIT 50";
        $docResult = pg_query_params($conn, $docQuery, $params);
    }

    $documents = pg_fetch_all($docResult) ?: [];

    // Total Document Count (Excluding returned status for accuracy)
    if ($userRole === 'osas' || $userRole === 'admin') {
        $docCountRes = pg_query($conn, "SELECT COUNT(*) FROM documents WHERE status != 'returned'");
    } else {
        $docCountRes = pg_query_params($conn, "SELECT COUNT(*) FROM documents WHERE org_id = $1 AND status != 'returned'", [$orgId]);
    }
    $totalDocs = (int)pg_fetch_result($docCountRes, 0, 0);

    echo json_encode([
        "userRole" => $userRole,
        "activities" => $activities,
        "documents" => $documents,
        "totalActivities" => count($activities), 
        "totalDocuments" => $totalDocs
    ]);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>