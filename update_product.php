<?php
include 'config.php';

$response = ["success" => false, "message" => ""];

// Get incoming values
$id               = $_POST['id'] ?? '';
$cat_id           = $_POST['cat_id'] ?? '';
$name             = $_POST['name'] ?? '';
$short_description= $_POST['short_description'] ?? '';
$description      = $_POST['description'] ?? '';
$isActive         = $_POST['isActive'] ?? 0;

// File inputs
$product_photo    = $_FILES['product_photo'] ?? null;
$multi_photos     = $_FILES['multi_photos'] ?? null;
$deleted_photos   = $_POST['deleted_photos'] ?? ''; // comma-separated URLs

if (empty($id)) {
    echo json_encode(["success" => false, "message" => "Product ID is required"]);
    exit;
}

// Fetch existing product
$existingRes = $conn->prepare("SELECT * FROM product WHERE id=?");
$existingRes->bind_param("i", $id);
$existingRes->execute();
$existingData = $existingRes->get_result()->fetch_assoc();

if (!$existingData) {
    echo json_encode(["success" => false, "message" => "Product not found"]);
    exit;
}

// ✅ 1. Category validation
if (!empty($cat_id)) {
    $checkCat = $conn->prepare("SELECT id FROM category WHERE id=?");
    $checkCat->bind_param("i", $cat_id);
    $checkCat->execute();
    $catExists = $checkCat->get_result()->num_rows > 0;
    if (!$catExists) {
        // Keep old if invalid
        $cat_id = $existingData['cat_id'];
    }
} else {
    $cat_id = $existingData['cat_id'];
}

// Upload folder
$uploadDir = "uploads/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ✅ 2. Handle single product photo
$productPhotoUrl = $existingData['product_photo'];
if ($product_photo && $product_photo['error'] == 0) {
    // Delete old if exists
    if (!empty($existingData['product_photo'])) {
        $oldPath = str_replace("http://{$_SERVER['HTTP_HOST']}/neuralink/", "", $existingData['product_photo']);
        @unlink($oldPath);
    }

    $ext = pathinfo($product_photo['name'], PATHINFO_EXTENSION);
    $newName = 'product_' . time() . '_' . uniqid() . '.' . $ext;
    $targetFile = $uploadDir . $newName;
    if (move_uploaded_file($product_photo['tmp_name'], $targetFile)) {
        $productPhotoUrl = "http://{$_SERVER['HTTP_HOST']}/neuralink/uploads/" . $newName;
    }
}

// ✅ 3. Handle multi-photos safely
$multiPhotosRaw = $existingData['multi_photos'] ?? '';

if (is_array($multiPhotosRaw)) {
    // If it's already an array (unexpected), implode it to string
    $multiPhotosRaw = implode(",", $multiPhotosRaw);
}

$existingMulti = [];

if (!empty($multiPhotosRaw)) {
    $existingMulti = array_filter(explode(",", $multiPhotosRaw));
}

// Remove deleted photos
if (!empty($deleted_photos)) {
    $deleteList = array_map('trim', explode(",", $deleted_photos));
    foreach ($deleteList as $url) {
        $filePath = str_replace("http://{$_SERVER['HTTP_HOST']}/neuralink/", "", $url);
        @unlink($filePath);
        // Remove from existing multi list
        $existingMulti = array_diff($existingMulti, [$url]);
    }
}

// Add new uploads if any
if (!empty($multi_photos['name'][0])) {
    foreach ($multi_photos['name'] as $key => $nameVal) {
        if ($multi_photos['error'][$key] == 0) {
            $ext = pathinfo($nameVal, PATHINFO_EXTENSION);
            $newName = 'product_' . time() . '_' . uniqid() . '.' . $ext;
            $targetFile = $uploadDir . $newName;
            if (move_uploaded_file($multi_photos['tmp_name'][$key], $targetFile)) {
                $existingMulti[] = "http://{$_SERVER['HTTP_HOST']}/neuralink/uploads/" . $newName;
            }
        }
    }
}

// Rebuild final multi_photos string
$multiPhotosUrl = implode(",", $existingMulti);

// ✅ 4. Update in DB
$stmt = $conn->prepare("
    UPDATE product 
    SET cat_id=?, name=?, short_description=?, description=?, product_photo=?, multi_photos=?, isActive=?
    WHERE id=?
");
$stmt->bind_param("isssssii", $cat_id, $name, $short_description, $description, $productPhotoUrl, $multiPhotosUrl, $isActive, $id);

if ($stmt->execute()) {
    $response["success"] = true;
    $response["message"] = "Product updated successfully";
} else {
    $response["message"] = "Update failed: " . $conn->error;
}

echo json_encode($response);
?>
