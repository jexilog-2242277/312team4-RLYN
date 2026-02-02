<?php
session_start();
header('Content-Type: application/json');
require_once "../includes/db_connect.php";

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['osas', 'admin'])) {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$type = $input['type'] ?? '';
$id = $input['id'] ?? null;
$reason = $input['reason'] ?? '';

if (!$id || !$type || empty($reason)) {
    echo json_encode(["success" => false, "error" => "Missing required information"]);
    exit;
}

try {
    // --- Determine table and update ---
    if ($type === 'activity') {
        $query = "UPDATE activities SET status = 'returned', return_reason = $1 WHERE activity_id = $2 RETURNING name, org_id";
    } else if ($type === 'document') {
        $query = "UPDATE documents SET status = 'returned', return_reason = $1 WHERE document_id = $2 RETURNING document_name AS name, org_id";
    } else {
        throw new Exception("Invalid type");
    }

    $result = pg_query_params($conn, $query, [$reason, $id]);

    if ($result && pg_num_rows($result) > 0) {
        $row = pg_fetch_assoc($result);
        $name = $row['name'];
        $orgId = $row['org_id'];

        // --- Insert notification ---
        $message = $type === 'activity' 
            ? "Activity '{$name}' has been returned by OSAS." 
            : "Document '{$name}' has been returned by OSAS.";

        $notifQuery = "
            INSERT INTO notifications (org_id, user_role, message, link)
            VALUES ($1, 'both', $2, $3)
        ";
        $link = "returned.php";
        pg_query_params($conn, $notifQuery, [$orgId, $message, $link]);

        echo json_encode(["success" => true, "message" => "Item returned and notification sent"]);
    } else {
        throw new Exception("Failed to update item");
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
