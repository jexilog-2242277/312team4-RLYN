<?php
// config/db.php
$host = "localhost";
$port = "5432";
$dbname = "312team4-RLYN";
$user = "postgres";
$password = "123";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    // module-style plain error
    die("Database connection failed.");
}
?>
