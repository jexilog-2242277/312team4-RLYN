<?php
session_start();
header('Content-Type: application/json');
require_once "../includes/db_connect.php";

// Only allow authorized roles
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['student', 'adviser'])) {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

$type = '';
$id = null;
$data = [];

// Handle multipart/form-data (file upload) or JSON input
if (!empty($_FILES)) {
    $type = $_POST['type'] ?? '';
    $id = $_POST['id'] ?? null;
    $name = $_POST['name'] ?? '';
    $academic_year = $_POST['academic_year'] ?? '';
    $sdg_relation = $_POST['sdg_relation'] ?? '';
    $description = $_POST['description'] ?? '';
} else {
    $input = json_decode(file_get_contents("php://input"), true);
    $type = $input['type'] ?? '';
    $id = $input['id'] ?? null;
    $name = $input['name'] ?? '';
    $academic_year = $input['academic_year'] ?? '';
    $sdg_relation = $input['sdg_relation'] ?? '';
    $description = $input['description'] ?? '';
}

if (!$id || !$type) {
    echo json_encode(["success" => false, "error" => "Missing required information"]);
    exit;
}

try {
    if ($type === 'activity') {

        // Update activity fields
        $query = "UPDATE activities 
                  SET name=$1, academic_year=$2, sdg_relation=$3, description=$4, status='submitted', return_reason=NULL
                  WHERE activity_id=$5";
        $result = pg_query_params($conn, $query, [$name, $academic_year, $sdg_relation, $description, $id]);

        if (!$result) throw new Exception(pg_last_error($conn));

        // Handle file upload if provided
        if (!empty($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['file'];
            $uploadDir = "../uploads/activities/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $filename = time() . "_" . basename($file['name']);
            $targetPath = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                // Insert into documents table linked to this activity
                $docQuery = "INSERT INTO documents (activity_id, name, file_path, status) VALUES ($1, $2, $3, 'submitted')";
                $docResult = pg_query_params($conn, $docQuery, [$id, $file['name'], $filename]);
                if (!$docResult) throw new Exception(pg_last_error($conn));
            } else {
                throw new Exception("Failed to upload file");
            }
        }

        echo json_encode(["success" => true, "message" => "Activity resubmitted successfully"]);

    } elseif ($type === 'document') {
        // For documents, only update status (assuming separate upload script handles file)
        $query = "UPDATE documents SET status='submitted', return_reason=NULL WHERE document_id=$1";
        $result = pg_query_params($conn, $query, [$id]);
        if (!$result) throw new Exception(pg_last_error($conn));

        echo json_encode(["success" => true, "message" => "Document resubmitted successfully"]);

    } else {
        throw new Exception("Invalid type");
    }

} catch(Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
