<?php
session_start();
header('Content-Type: application/json');
require_once "../includes/db_connect.php";

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "Not logged in"]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);

$id = $input['id'] ?? null;
$name = trim($input['name'] ?? '');
$academic_year = trim($input['academic_year'] ?? '');
$sdg_relation = trim($input['sdg_relation'] ?? '');
$return_reason = trim($input['return_reason'] ?? '');

if (!$id || !$name || !$academic_year || !$sdg_relation) {
    echo json_encode(["success" => false, "error" => "Missing required fields"]);
    exit;
}

// Fetch the activity to check authorization
$queryCheck = "SELECT org_id, status FROM activities WHERE activity_id = $1";
$resultCheck = pg_query_params($conn, $queryCheck, [$id]);

if (pg_num_rows($resultCheck) === 0) {
    echo json_encode(["success" => false, "error" => "Activity not found"]);
    exit;
}

$activity = pg_fetch_assoc($resultCheck);

// Authorization: user must belong to same org and NOT be osas/admin
$userOrgId = $_SESSION['org_id'] ?? null;
$userRole = $_SESSION['role'] ?? '';

if (!$userOrgId || $userOrgId != $activity['org_id'] || in_array($userRole, ['osas','admin'])) {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

// Only allow editing returned activities
if ($activity['status'] !== 'returned') {
    echo json_encode(["success" => false, "error" => "Activity is not returned"]);
    exit;
}

// Update the activity
$queryUpdate = "UPDATE activities 
                SET name = $1, academic_year = $2, sdg_relation = $3, return_reason = $4
                WHERE activity_id = $5";

$result = pg_query_params($conn, $queryUpdate, [$name, $academic_year, $sdg_relation, $return_reason, $id]);

if ($result) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => pg_last_error($conn)]);
}
?>
