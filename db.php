<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "foodforensics";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    header('Content-Type: application/json');
    die(json_encode(["error" => "Database Connection Failed: " . $conn->connect_error]));
}

// Set charset to avoid broken symbols in ingredient names
$conn->set_charset("utf8mb4");
?>