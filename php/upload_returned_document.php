<?php
session_start();
header('Content-Type: application/json');
require_once "../includes/db_connect.php";

// --- Authorization ---
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['student','adviser'])) {
    echo json_encode(["success"=>false,"error"=>"Unauthorized"]);
    exit;
}

// --- Validate input ---
if(!isset($_FILES['file']) || !isset($_POST['document_id'])){
    echo json_encode(["success"=>false,"error"=>"Missing file or document ID"]);
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

if(!in_array($file['type'],$allowed) || $file['error']!==UPLOAD_ERR_OK){
    echo json_encode(["success"=>false,"error"=>"Invalid file or upload error"]);
    exit;
}

// --- Upload directory ---
$uploadDir = "../uploads/documents/";
if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

// --- Get org_id from the document's activity to satisfy FK ---
$docOrgRes = pg_query_params($conn, "
    SELECT a.org_id 
    FROM documents d
    JOIN activities a ON d.activity_id = a.activity_id
    WHERE d.document_id=$1
", [$documentId]);

if(!$docOrgRes || pg_num_rows($docOrgRes) === 0){
    echo json_encode(["success"=>false,"error"=>"Document or associated activity not found"]);
    exit;
}

$orgId = pg_fetch_result($docOrgRes, 0, 'org_id');

// --- Sanitize filename ---
$originalName = pathinfo($file['name'], PATHINFO_FILENAME);
$originalName = preg_replace("/[^a-zA-Z0-9_-]/", "_", $originalName);
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$newFileName = "doc_{$documentId}_" . date("Ymd_His") . "." . $ext;
$destination = $uploadDir . $newFileName;

// --- Move file ---
if(!move_uploaded_file($file['tmp_name'],$destination)){
    echo json_encode(["success"=>false,"error"=>"Failed to move uploaded file"]);
    exit;
}

// --- Update document record ---
$query = "
    UPDATE documents
    SET document_file_path=$1,
        status='submitted',
        return_reason=NULL,
        uploaded_at=NOW(),
        uploaded_by=$2,
        org_id=$3
    WHERE document_id=$4
";

$result = pg_query_params($conn, $query, [$newFileName, $userId, $orgId, $documentId]);

if($result){
    echo json_encode([
        "success"=>true,
        "message"=>"Document resubmitted successfully",
        "file_name"=>$newFileName
    ]);
} else {
    // Delete file if DB update failed
    if(file_exists($destination)) unlink($destination);
    echo json_encode(["success"=>false,"error"=>pg_last_error($conn)]);
}

pg_close($conn);
?>
