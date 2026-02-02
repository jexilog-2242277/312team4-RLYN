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
    if ($type === 'activity') {
        $query = "UPDATE activities SET status = 'returned', return_reason = $1 WHERE activity_id = $2";
    } else if ($type === 'document') {
        $query = "UPDATE documents SET status = 'returned', return_reason = $1 WHERE document_id = $2";
    } else {
        throw new Exception("Invalid type");
    }

    $result = pg_query_params($conn, $query, [$reason, $id]);

    if ($result) {
        echo json_encode(["success" => true]);
    } else {
        throw new Exception(pg_last_error($conn));
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>