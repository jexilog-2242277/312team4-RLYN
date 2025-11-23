<?php
// config/db.php
$host = "localhost";
$port = "2048";
$dbname = "312team4-RLYN-mid";
$user = "postgres";
$password = "admin";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    // module-style plain error
    die("Database connection failed.");
}
?>
