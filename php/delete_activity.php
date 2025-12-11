<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "Not logged in"]);
    exit;
}

require_once "../includes/db_connect.php";  // PostgreSQL connection

// Read JSON input
$input = json_decode(file_get_contents("php://input"), true);

$type = $input['type'] ?? 'activity';
$id   = $input['activity_id'] ?? ($input['id'] ?? null);

if (!$id) {
    echo json_encode(["success" => false, "error" => "Missing ID"]);
    exit;
}

try {
    if ($type === "activity") {
        // Delete documents first (foreign key constraint)
        $result1 = pg_query_params($conn,
            "DELETE FROM documents WHERE activity_id = $1",
            [$id]
        );

        if (!$result1) {
            throw new Exception("Failed to delete associated documents: " . pg_last_error($conn));
        }

        // Delete the activity
        $result2 = pg_query_params($conn,
            "DELETE FROM activities WHERE activity_id = $1",
            [$id]
        );

        if (!$result2) {
            throw new Exception("Failed to delete activity: " . pg_last_error($conn));
        }

        // Check if anything was actually deleted
        $rowsAffected = pg_affected_rows($result2);
        if ($rowsAffected === 0) {
            echo json_encode(["success" => false, "error" => "Activity not found or already deleted"]);
            exit;
        }

        echo json_encode(["success" => true, "message" => "Activity and associated documents deleted"]);
        exit;
    }

    if ($type === "document") {
        $result = pg_query_params($conn,
            "DELETE FROM documents WHERE document_id = $1",
            [$id]
        );

        if (!$result) {
            throw new Exception("Failed to delete document: " . pg_last_error($conn));
        }

        // Check if anything was actually deleted
        $rowsAffected = pg_affected_rows($result);
        if ($rowsAffected === 0) {
            echo json_encode(["success" => false, "error" => "Document not found or already deleted"]);
            exit;
        }

        echo json_encode(["success" => true, "message" => "Document deleted"]);
        exit;
    }

    echo json_encode(["success" => false, "error" => "Invalid delete type"]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>