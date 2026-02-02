<?php
session_start();
header('Content-Type: application/json');
require_once "../includes/db_connect.php";

// Only allow students or advisers of the org that owns the activity
$userId = $_SESSION['user_id'] ?? null;
$userRole = $_SESSION['role'] ?? null;

if (!$userId || !in_array($userRole, ['student', 'adviser'])) {
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

// Verify that the user is allowed to edit this activity
// For students/advisers: check that they are the owner or part of the org
try {
    $queryCheck = "SELECT org_id, student_id FROM activities WHERE activity_id = $1";
    $resultCheck = pg_query_params($conn, $queryCheck, [$id]);

    if (!$resultCheck || pg_num_rows($resultCheck) === 0) {
        echo json_encode(["success" => false, "error" => "Activity not found"]);
        exit;
    }

    $activity = pg_fetch_assoc($resultCheck);

    // Students can only edit their own activities
    if ($userRole === 'student' && $activity['student_id'] != $userId) {
        echo json_encode(["success" => false, "error" => "Unauthorized"]);
        exit;
    }

    // Advisers can only edit activities of their org
    if ($userRole === 'adviser') {
        // Replace this with your method to get the adviser's org_id
        $queryOrg = "SELECT org_id FROM advisers WHERE adviser_id = $1";
        $resOrg = pg_query_params($conn, $queryOrg, [$userId]);
        $adviser = pg_fetch_assoc($resOrg);

        if (!$adviser || $adviser['org_id'] != $activity['org_id']) {
            echo json_encode(["success" => false, "error" => "Unauthorized"]);
            exit;
        }
    }

    // Proceed to update
    $queryUpdate = "UPDATE activities 
                    SET name = $1, academic_year = $2, sdg_relation = $3, description = $4 
                    WHERE activity_id = $5";

    $resultUpdate = pg_query_params($conn, $queryUpdate, [$name, $academic_year, $sdg_relation, $description, $id]);

    if ($resultUpdate) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => pg_last_error($conn)]);
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
