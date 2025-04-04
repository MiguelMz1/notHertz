<?php
header("Cache-Control: no-cache");
$host = "localhost";
$username = "----";
$password = "----"; //setting this to --- for now
$database = "----";

$conn = new mysqli($servername, $username, $password,$dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
} 
?>