<?php
include "db.php";

$name    = $_POST['name'] ?? '';
$age     = $_POST['age'] ?? '';
$height  = $_POST['height'] ?? '';
$weight  = $_POST['weight'] ?? '';
$disease = $_POST['disease'] ?? '';

$sql = "INSERT INTO user_profiles (name, age, height_cm, weight_kg, diseases)
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("siiis", $name, $age, $height, $weight, $disease);

if($stmt->execute()){
    echo "success";
}else{
    echo "error";
}
?>