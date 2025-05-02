<?php
header("Cache-Control: no-cache");


$host = "localhost";
$username = "xxxxxxxxxxxx";
$password = "xxxxxxxxxxxx";
$database = "xxxxxxxxxxxx";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection error.");
}

$conn->set_charset("utf8mb4");
?>