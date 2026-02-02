<?php
session_start();
header('Content-Type: application/json');
require_once "../includes/db_connect.php";

$input = json_decode(file_get_contents("php://input"), true);
$id = $input['id'] ?? null;
$name = $input['name'] ?? '';
$academic_year = $input['academic_year'] ?? '';
$sdg_relation = $input['sdg_relation'] ?? '';
$description = $input['description'] ?? '';

if(!$id){
    echo json_encode(["success"=>false, "error"=>"Missing activity ID"]);
    exit;
}

try {
    $query = "UPDATE activities 
              SET name=$1, academic_year=$2, sdg_relation=$3, description=$4
              WHERE activity_id=$5";
    $result = pg_query_params($conn, $query, [$name, $academic_year, $sdg_relation, $description, $id]);

    if($result){
        echo json_encode(["success"=>true]);
    } else {
        throw new Exception(pg_last_error($conn));
    }
} catch(Exception $e){
    echo json_encode(["success"=>false, "error"=>$e->getMessage()]);
}
?>
