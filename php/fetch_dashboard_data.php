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

$searchTerm = $_GET['search'] ?? '';

try {
    $activitySearchFilter = '';
    $documentSearchFilter = ''; 
    $params = [];
    $paramIndex = 1;

    // Search Filter Construction 
    if ($searchTerm !== '') {
        // MODIFICATION: Use CONTAINS wildcard for flexible word matching in all relevant fields
        $searchWord = '%' . $searchTerm . '%'; 

        // Filter for Activities (a.name, a.sdg_relation, a.academic_year now use CONTAINS):
        $activitySearchFilter = " AND (
            a.name ILIKE $" . $paramIndex . " OR
            a.sdg_relation ILIKE $" . $paramIndex . " OR
            a.academic_year ILIKE $" . $paramIndex . "
        )";
        
        // Filter for Documents (d.document_name, d.document_type also use CONTAINS):
        $documentSearchFilter = " AND (
            a.name ILIKE $" . $paramIndex . " OR
            a.sdg_relation ILIKE $" . $paramIndex . " OR
            a.academic_year ILIKE $" . $paramIndex . " OR
            d.document_name ILIKE $" . $paramIndex . " OR 
            d.document_type ILIKE $" . $paramIndex . " 
        )";

        $params[] = $searchWord; 
        $paramIndex++;
    }


    // Activity Query Execution 

    if ($userRole === 'osas' || $userRole === 'admin') {
        $query = "SELECT a.*, o.name AS org_name,
                  (SELECT COUNT(*) FROM documents d WHERE d.activity_id = a.activity_id) AS doc_count
                  FROM activities a
                  JOIN organizations o ON a.org_id = o.org_id
                  WHERE 1=1 " . $activitySearchFilter . "
                  ORDER BY a.created_at DESC";
        
        if ($searchTerm !== '') {
            $result = pg_query_params($conn, $query, $params);
        } else {
            $result = pg_query($conn, $query);
        }

    } else if ($orgId) {
        
        // ORGANIZATION USER: Parameter Handling
        array_unshift($params, $orgId); 
        
        $orgFilter = " a.org_id = $1"; 

        // Rebuild filters to explicitly use $2 for the search term
        if ($searchTerm !== '') {
             
             // All fields use the CONTAINS pattern ($2)
             $activitySearchFilter = " AND (
                a.name ILIKE $2 OR 
                a.sdg_relation ILIKE $2 OR 
                a.academic_year ILIKE $2
            )";
            $documentSearchFilter = " AND (
                a.name ILIKE $2 OR 
                a.sdg_relation ILIKE $2 OR 
                a.academic_year ILIKE $2 OR
                d.document_name ILIKE $2 OR 
                d.document_type ILIKE $2 
            )";
        }
        
        $query = "SELECT a.*, o.name AS org_name,
                  (SELECT COUNT(*) FROM documents d WHERE d.activity_id = a.activity_id) AS doc_count
                  FROM activities a
                  JOIN organizations o ON a.org_id = o.org_id
                  WHERE " . $orgFilter . $activitySearchFilter . "
                  ORDER BY a.created_at DESC";
        
        $result = pg_query_params($conn, $query, $params);
        
    } else {
        echo json_encode(["error" => "No organization ID found"]);
        exit;
    }

    $activities = pg_fetch_all($result) ?: [];

    foreach ($activities as &$act) {
        if (isset($act['doc_count'])) {
            $act['doc_count'] = (int)$act['doc_count'];
        } else {
            $act['doc_count'] = 0;
        }
    }
    unset($act);

    // Document Query Execution 
    
    $docParams = $params;

    if ($userRole === 'osas' || $userRole === 'admin') {
        $docQuery = "SELECT d.*, o.name AS org_name, a.name AS activity_name
                     FROM documents d
                     LEFT JOIN organizations o ON d.org_id = o.org_id
                     JOIN activities a ON d.activity_id = a.activity_id
                     WHERE 1=1 " . $documentSearchFilter . " 
                     ORDER BY d.uploaded_at DESC
                     LIMIT 50";
        if ($searchTerm !== '') {
            $docResult = pg_query_params($conn, $docQuery, $docParams);
        } else {
            $docResult = pg_query($conn, $docQuery);
        }
    } else {
        $orgDocFilter = " d.org_id = $1";
        
        $docQuery = "SELECT d.*, o.name AS org_name, a.name AS activity_name
                     FROM documents d
                     LEFT JOIN organizations o ON d.org_id = o.org_id
                     JOIN activities a ON d.activity_id = a.activity_id
                     WHERE " . $orgDocFilter . $documentSearchFilter . " 
                     ORDER BY d.uploaded_at DESC
                     LIMIT 50";
        $docResult = pg_query_params($conn, $docQuery, $docParams);
    }

    $documents = pg_fetch_all($docResult) ?: [];

    // Total Document Count (Unfiltered)
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