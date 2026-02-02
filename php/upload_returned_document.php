<?php
session_start();
header('Content-Type: application/json');
require_once "../includes/db_connect.php";

// --- Authorization ---
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['student','adviser'])) {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

// --- Validate input ---
if (!isset($_FILES['file']) || !isset($_POST['document_id'])) {
    echo json_encode(["success" => false, "error" => "Missing file or document ID"]);
    exit;
}

$documentId = intval($_POST['document_id']);
$file = $_FILES['file'];
$userId = $_SESSION['user_id'];

// --- Allowed file types ---
$allowed = [
    'application/pdf',
    'text/csv',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
];

if (!in_array($file['type'], $allowed) || $file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["success" => false, "error" => "Invalid file or upload error"]);
    exit;
}

// --- Upload directory ---
$uploadDir = "../uploads/documents/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

// --- Fetch old document info ---
$docRes = pg_query_params($conn, "SELECT document_name, document_file_path, activity_id FROM documents WHERE document_id=$1", [$documentId]);
if (!$docRes || pg_num_rows($docRes) === 0) {
    echo json_encode(["success" => false, "error" => "Document not found"]);
    exit;
}

$oldDoc = pg_fetch_assoc($docRes);
$activityId = $oldDoc['activity_id'];
$oldFilePath = $oldDoc['document_file_path'];
$oldName = $oldDoc['document_name'];

// --- Get org_id from activity to satisfy FK ---
$orgRes = pg_query_params($conn, "SELECT org_id FROM activities WHERE activity_id=$1", [$activityId]);
if (!$orgRes || pg_num_rows($orgRes) === 0) {
    echo json_encode(["success" => false, "error" => "Associated activity not found"]);
    exit;
}
$orgId = pg_fetch_result($orgRes, 0, 'org_id');

// --- Sanitize filename ---
$originalName = pathinfo($file['name'], PATHINFO_FILENAME);
$originalName = preg_replace("/[^a-zA-Z0-9_-]/", "_", $originalName);
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$newFileName = "doc_{$documentId}_" . date("Ymd_His") . "." . $ext;
$destination = $uploadDir . $newFileName;

// --- Move uploaded file ---
if (!move_uploaded_file($file['tmp_name'], $destination)) {
    echo json_encode(["success" => false, "error" => "Failed to move uploaded file"]);
    exit;
}


// --- Update document record ---
$updateRes = pg_query_params($conn, "
    UPDATE documents
    SET document_file_path=$1,
        document_name=$2,
        status='submitted',
        return_reason=NULL,
        uploaded_at=NOW(),
        uploaded_by=$3,
        org_id=$4
    WHERE document_id=$5
", [$newFileName, $file['name'], $userId, $orgId, $documentId]);

if ($updateRes) {
    // Delete old file from server if exists
    if ($oldFilePath && file_exists($uploadDir . $oldFilePath)) {
        unlink($uploadDir . $oldFilePath);
    }

    echo json_encode([
        "success" => true,
        "message" => "Document resubmitted successfully",
        "file_name" => $file['name'],
        "file_path" => $newFileName
    ]);
} else {
    // Rollback: remove new uploaded file if DB update failed
    if (file_exists($destination)) unlink($destination);
    echo json_encode(["success" => false, "error" => pg_last_error($conn)]);
}

pg_close($conn);
?>
