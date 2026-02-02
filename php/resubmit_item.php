<?php
session_start();
header('Content-Type: application/json');
require_once "../includes/db_connect.php";

// Only allow org members (students, advisers) to resubmit
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['student', 'adviser'])) {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$type = $input['type'] ?? '';
$id = $input['id'] ?? null;

if (!$id || !$type) {
    echo json_encode(["success" => false, "error" => "Missing required information"]);
    exit;
}

try {
    if ($type === 'activity') {
        $query = "UPDATE activities SET status = 'submitted', return_reason = NULL WHERE activity_id = $1";
    } else if ($type === 'document') {
        $query = "UPDATE documents SET status = 'submitted', return_reason = NULL WHERE document_id = $1";
    } else {
        throw new Exception("Invalid type");
    }

    $result = pg_query_params($conn, $query, [$id]);

    if ($result) {
        echo json_encode(["success" => true, "message" => ucfirst($type)." resubmitted successfully"]);
    } else {
        throw new Exception(pg_last_error($conn));
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
