<?php
// config/db.php
$host = "localhost";
$port = "5432";
$dbname = "312team-RLYN-mid";
$user = "postgres";
$password = "Imthelegendboy3!";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    // module-style plain error
    die("Database connection failed.");
}
?>
