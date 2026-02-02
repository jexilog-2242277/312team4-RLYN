<?php
session_start();
header('Content-Type: application/json');
require_once "../includes/db_connect.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success"=>false,"error"=>"Unauthorized"]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$notifId = $input['notif_id'] ?? null;

if (!$notifId) {
    echo json_encode(["success"=>false,"error"=>"Notification ID missing"]);
    exit;
}

$result = pg_query_params($conn, "UPDATE notifications SET read_status = true WHERE notif_id = $1", [$notifId]);

if ($result) echo json_encode(["success"=>true]);
else echo json_encode(["success"=>false,"error"=>pg_last_error($conn)]);
?>
