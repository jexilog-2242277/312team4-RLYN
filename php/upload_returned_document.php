<?php
session_start();
header('Content-Type: application/json');
require_once "../includes/db_connect.php";

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['student','adviser'])) {
    echo json_encode(["success"=>false,"error"=>"Unauthorized"]);
    exit;
}

if(!isset($_FILES['file']) || !isset($_POST['document_id'])){
    echo json_encode(["success"=>false,"error"=>"Missing file or document ID"]);
    exit;
}

$documentId = intval($_POST['document_id']);
$file = $_FILES['file'];
$userId = $_SESSION['user_id'];

$allowed = ['application/pdf','text/csv','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
if(!in_array($file['type'],$allowed) || $file['error']!==UPLOAD_ERR_OK){
    echo json_encode(["success"=>false,"error"=>"Invalid file or upload error"]);
    exit;
}

$uploadDir = "../uploads/";
if(!is_dir($uploadDir)) mkdir($uploadDir,0755,true);

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$newFileName = "doc_{$documentId}_".date("Ymd_His").".".$ext;
$destination = $uploadDir.$newFileName;

if(!move_uploaded_file($file['tmp_name'],$destination)){
    echo json_encode(["success"=>false,"error"=>"Failed to move uploaded file"]);
    exit;
}

$query = "UPDATE documents SET document_file_path=$1, status='submitted', return_reason=NULL, uploaded_at=NOW(), uploaded_by=$2 WHERE document_id=$3";
$result = pg_query_params($conn,$query,[$newFileName,$userId,$documentId]);

if($result) echo json_encode(["success"=>true,"message"=>"Document resubmitted successfully"]);
else echo json_encode(["success"=>false,"error"=>pg_last_error($conn)]);
?>
