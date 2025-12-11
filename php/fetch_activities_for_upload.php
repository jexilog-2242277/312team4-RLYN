<?php
session_start();
header('Content-Type: application/json');
include '../includes/db_connect.php'; // Ensure this path is correct

if (!isset($_SESSION['user_id']) || !isset($_SESSION['org_id'])) {
    echo json_encode(["success" => false, "message" => "Authentication required."]);
    exit;
}

$orgId = $_SESSION['org_id'];
$userRole = $_SESSION['role'] ?? 'user';

// --- Determine Activity Fetching Scope ---
// If the user is a normal user, fetch only their org's activities.
// If the user is 'osas' or 'admin', they might need to see all activities.
// Assuming for an organization user, they only see their organization's activities.

$query = "
    SELECT activity_id, name
    FROM activities
    WHERE org_id = $1
    ORDER BY name ASC
";

$result = pg_query_params($conn, $query, [$orgId]);

if ($result) {
    $activities = pg_fetch_all($result) ?: [];
    
    // Check if any rows were returned
    if (!empty($activities)) {
        echo json_encode(["success" => true, "activities" => $activities]);
    } else {
        echo json_encode(["success" => true, "activities" => [], "message" => "No activities found for this organization."]);
    }
} else {
    // Log error in production, send generic error to client
    // error_log("Database error fetching activities: " . pg_last_error($conn));
    echo json_encode(["success" => false, "message" => "Failed to retrieve activity list from database."]);
}

pg_close($conn);
?>