<?php
$host = "localhost";
$user = "root";
$pass = "Larrinaga9696";
$db = "novedades_economica";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Error BD: " . $conn->connect_error);
$conn->set_charset("utf8mb4");
?>