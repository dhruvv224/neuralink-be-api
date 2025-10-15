<?php
include 'config.php';
header('Content-Type: application/json');

$id          = $_POST['id'] ?? '';
$name        = $_POST['name'] ?? '';
$description = $_POST['description'] ?? '';
$isActive    = $_POST['isActive'] ?? 0;

if (empty($id)) {
    echo json_encode(["success" => false, "message" => "ID is required"]);
    exit;
}

// Get old image before updating
$oldPhoto = '';
$result = $conn->query("SELECT photo FROM category WHERE id = " . intval($id));
if ($result && $row = $result->fetch_assoc()) {
    $oldPhoto = $row['photo'];
}

// Default photo URL is the old one
$photoUrl = $oldPhoto;

// Upload folder
$uploadDir = "uploads/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// If new photo is uploaded, replace old one
if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
    // Delete old image file if exists
    if (!empty($oldPhoto)) {
        $oldPath = str_replace("http://{$_SERVER['HTTP_HOST']}/neuralink/", "", $oldPhoto);
        if (file_exists($oldPath)) {
            unlink($oldPath);
        }
    }

    // Save new image
    $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $newName = 'cat_' . time() . '_' . uniqid() . '.' . $ext;
    $targetFile = $uploadDir . $newName;

    if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
        $photoUrl = "http://{$_SERVER['HTTP_HOST']}/neuralink/uploads/" . $newName;
    }
}

// Update record
$stmt = $conn->prepare("UPDATE category SET name=?, photo=?, description=?, isActive=? WHERE id=?");
$stmt->bind_param("sssii", $name, $photoUrl, $description, $isActive, $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Category updated"]);
} else {
    echo json_encode(["success" => false, "message" => "Update failed: " . $conn->error]);
}
?>
