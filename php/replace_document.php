<?php
session_start();
header('Content-Type: application/json');
require_once "../includes/db_connect.php";

if(!isset($_SESSION['user_id'])){
    echo json_encode(["success"=>false,"error"=>"Unauthorized"]);
    exit;
}

if(!isset($_FILES['file']) || !isset($_POST['document_id'])){
    echo json_encode(["success"=>false,"error"=>"Missing file or document ID"]);
    exit;
}

$doc_id = $_POST['document_id'];
$file = $_FILES['file'];
$upload_dir = "../uploads/documents/";

if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

$filename = time().'_'.$file['name'];
$target_path = $upload_dir.$filename;

if(move_uploaded_file($file['tmp_name'], $target_path)){
    $query = "UPDATE documents SET file_path=$1, status='submitted', return_reason=NULL WHERE document_id=$2";
    $result = pg_query_params($conn, $query, [$filename, $doc_id]);

    if($result){
        echo json_encode(["success"=>true]);
    } else {
        echo json_encode(["success"=>false,"error"=>pg_last_error($conn)]);
    }
} else {
    echo json_encode(["success"=>false,"error"=>"Failed to move uploaded file"]);
}
?>
