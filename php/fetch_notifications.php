<?php
session_start();
header('Content-Type: application/json');
require_once "../includes/db_connect.php";

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['student', 'adviser'])) {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

$userId = $_SESSION['user_id'];
$orgId = $_SESSION['org_id'];

if (!$orgId) {
    echo json_encode(["success" => false, "error" => "Organization not found"]);
    exit;
}

try {
    $query = "
        SELECT notif_id, message, link, created_at
        FROM notifications
        WHERE org_id = $1 AND (user_role='both' OR user_role=$2) AND read_status = false
        ORDER BY created_at DESC
    ";

    $result = pg_query_params($conn, $query, [$orgId, $_SESSION['role']]);

    $notifications = [];
    while ($row = pg_fetch_assoc($result)) {
        $notifications[] = $row;
    }

    echo json_encode(["success" => true, "notifications" => $notifications]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
