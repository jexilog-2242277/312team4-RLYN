<?php
session_start();
header('Content-Type: application/json');
require_once "../includes/db_connect.php";

// --- Config ---
$uploadDir = '../uploads/documents/'; 
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Authentication failed."]);
    exit;
}

$type = $_POST['type'] ?? '';
$id = $_POST['id'] ?? null;

if (!$type || !$id) {
    echo json_encode(["success" => false, "message" => "Missing type or ID."]);
    exit;
}

try {
    if ($type === 'activity') {
        // Collect form data
        $name = $_POST['name'] ?? '';
        $academic_year = $_POST['academic_year'] ?? '';
        $sdg_relation = $_POST['sdg_relation'] ?? '';
        $description = $_POST['description'] ?? '';

        // Update activity info
        $updateQuery = "
            UPDATE activities 
            SET name=$1, academic_year=$2, sdg_relation=$3, description=$4, status='submitted', return_reason=NULL
            WHERE activity_id=$5
        ";
        $res = pg_query_params($conn, $updateQuery, [$name, $academic_year, $sdg_relation, $description, $id]);
        if (!$res) throw new Exception(pg_last_error($conn));

        // Get org_id from activity (for documents)
        $orgRes = pg_query_params($conn, "SELECT org_id FROM activities WHERE activity_id=$1", [$id]);
        if (!$orgRes) throw new Exception("Failed to fetch org_id.");
        $orgId = pg_fetch_result($orgRes, 0, 'org_id');

        // Handle optional file upload
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['file']['tmp_name'];
            $originalFileName = basename($_FILES['file']['name']);
            $fileType = $_FILES['file']['type'];

            $fileBase = preg_replace("/[^a-zA-Z0-9_-]/", "_", pathinfo($originalFileName, PATHINFO_FILENAME));
            $timestamp = date("Ymd_His");
            $newFileName = $fileBase . '_' . $timestamp . '.' . pathinfo($originalFileName, PATHINFO_EXTENSION);
            $destPath = $uploadDir . $newFileName;

            if (!move_uploaded_file($fileTmpPath, $destPath)) {
                throw new Exception("Failed to move uploaded file.");
            }

            // Insert document record
            $docQuery = "
                INSERT INTO documents 
                (org_id, activity_id, document_name, document_file_path, document_type, uploaded_by, status) 
                VALUES ($1, $2, $3, $4, $5, $6, 'submitted')
            ";
            $docRes = pg_query_params($conn, $docQuery, [
                $orgId,
                $id,
                $originalFileName,
                $newFileName,
                $fileType,
                $_SESSION['user_id']
            ]);
            if (!$docRes) throw new Exception("Failed to insert document into DB.");
        }

        echo json_encode(["success" => true, "message" => "Activity updated and resubmitted successfully."]);

    } elseif ($type === 'document') {
        // Resubmit document only
        $docQuery = "UPDATE documents SET status='submitted', return_reason=NULL WHERE document_id=$1";
        $res = pg_query_params($conn, $docQuery, [$id]);
        if (!$res) throw new Exception(pg_last_error($conn));

        echo json_encode(["success" => true, "message" => "Document resubmitted successfully."]);
    } else {
        throw new Exception("Invalid type.");
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

pg_close($conn);
