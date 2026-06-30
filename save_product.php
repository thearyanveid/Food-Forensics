<?php
include "db.php";

$ingredients = $_POST['ingredients'] ?? '';
$allergens   = $_POST['allergens'] ?? '';
$barcode     = $_POST['barcode'] ?? '';

$imagePath = "";
if(isset($_FILES['image'])){
    $imgName = time() . "_" . $_FILES['image']['name'];
    $target  = "uploads/" . $imgName;
    move_uploaded_file($_FILES['image']['tmp_name'], $target);
    $imagePath = $target;
}

$sql = "INSERT INTO products (ingredients, allergens, barcode, image_path)
        VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $ingredients, $allergens, $barcode, $imagePath);

if($stmt->execute()){
    echo "success";
}else{
    echo "error";
}
?>