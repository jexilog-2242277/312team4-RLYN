<?php
session_start();
header('Content-Type: application/json');
require_once "../includes/db_connect.php";

// Only admins and osas can edit returned activities
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['osas', 'admin'])) {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);

$id = $input['id'] ?? null;
$name = $input['name'] ?? '';
$academic_year = $input['academic_year'] ?? '';
$sdg_relation = $input['sdg_relation'] ?? '';
$description = $input['description'] ?? '';

if (!$id || empty($name)) {
    echo json_encode(["success" => false, "error" => "Missing required fields"]);
    exit;
}

try {
    $query = "UPDATE activities 
              SET name = $1, academic_year = $2, sdg_relation = $3, description = $4 
              WHERE activity_id = $5";

    $result = pg_query_params($conn, $query, [$name, $academic_year, $sdg_relation, $description, $id]);

    if ($result) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => pg_last_error($conn)]);
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
