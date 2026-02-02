<?php
session_start();
header('Content-Type: application/json');
require_once "../includes/db_connect.php";

// Only allow authorized roles
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['student', 'adviser'])) {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

// Determine input type
$type = $_POST['type'] ?? '';
$id = $_POST['id'] ?? null;

if (!$type || !$id) {
    echo json_encode(["success" => false, "error" => "Missing required information"]);
    exit;
}

try {
    if ($type === 'activity') {
        // Collect activity fields
        $name = $_POST['name'] ?? '';
        $academic_year = $_POST['academic_year'] ?? '';
        $sdg_relation = $_POST['sdg_relation'] ?? '';
        $description = $_POST['description'] ?? '';

        // Update activity
        $query = "UPDATE activities 
                  SET name=$1, academic_year=$2, sdg_relation=$3, description=$4, status='submitted', return_reason=NULL
                  WHERE activity_id=$5";
        $result = pg_query_params($conn, $query, [$name, $academic_year, $sdg_relation, $description, $id]);

        if (!$result) throw new Exception(pg_last_error($conn));

        // Handle optional file upload
        if (!empty($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['file'];
            $uploadDir = "../uploads/activities/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $timestamp = time();
            $filename = $timestamp . "_" . basename($file['name']);
            $targetPath = $uploadDir . $filename;

            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new Exception("Failed to upload file");
            }

            // Insert document record
            $docQuery = "INSERT INTO documents 
                         (org_id, activity_id, document_name, document_type, uploaded_at, document_file_path, uploaded_by, status) 
                         VALUES ($1, $2, $3, $4, NOW(), $5, $6, 'submitted')";
            $docResult = pg_query_params($conn, $docQuery, [
                $_SESSION['user_id'], 
                $id, 
                $file['name'], 
                mime_content_type($targetPath), 
                $filename, 
                $_SESSION['user_id']
            ]);

            if (!$docResult) throw new Exception(pg_last_error($conn));
        }

        echo json_encode(["success" => true, "message" => "Activity resubmitted successfully"]);

    } elseif ($type === 'document') {
        // Update document status only
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
