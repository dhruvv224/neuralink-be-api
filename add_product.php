<?php
include 'config.php';

header('Content-Type: application/json');

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Collect POST fields
    $cat_id        = $_POST['cat_id'] ?? '';
    $name          = $_POST['name'] ?? '';
    $short_desc    = $_POST['short_description'] ?? '';
    $description   = $_POST['description'] ?? '';

    // Validate required fields
    if (empty($cat_id) || empty($name) || empty($short_desc) || empty($description)) {
        $response['success'] = false;
        $response['message'] = "All fields are required.";
        echo json_encode($response);
        exit;
    }

    // Upload folder
    $uploadDir = "uploads/";
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Helper function for unique filenames
    function generateFileName($prefix, $ext) {
        return $prefix . time() . '_' . uniqid() . '.' . $ext;
    }

    // Single product image
    $productPhotoUrl = '';
    if (isset($_FILES['product_photo']) && $_FILES['product_photo']['error'] == 0) {
        $ext = pathinfo($_FILES['product_photo']['name'], PATHINFO_EXTENSION);
        $newName = generateFileName('product_', $ext);
        $targetFile = $uploadDir . $newName;
        if (move_uploaded_file($_FILES['product_photo']['tmp_name'], $targetFile)) {
            $productPhotoUrl = "http://{$_SERVER['HTTP_HOST']}/neuralink/uploads/" . $newName;
        }
    }

    // Multiple product images
    $multiPhotos = [];
    if (isset($_FILES['multi_photos']['name']) && is_array($_FILES['multi_photos']['name'])) {
        foreach ($_FILES['multi_photos']['name'] as $key => $fileName) {
            if ($_FILES['multi_photos']['error'][$key] === 0) {
                $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                $newName = generateFileName('product_', $ext);
                $targetFile = $uploadDir . $newName;
                if (move_uploaded_file($_FILES['multi_photos']['tmp_name'][$key], $targetFile)) {
                    $multiPhotos[] = "http://{$_SERVER['HTTP_HOST']}/neuralink/uploads/" . $newName;
                }
            }
        }
    }

    // Convert array to comma-separated string
    $multiPhotosString = mysqli_real_escape_string($conn, implode(',', $multiPhotos));

    // Insert into product table
    $sql = "INSERT INTO product 
            (cat_id, name, short_description, description, product_photo, multi_photos, isActive)
            VALUES 
            ('$cat_id', '$name', '$short_desc', '$description', '$productPhotoUrl', '$multiPhotosString', 0)";

    if (mysqli_query($conn, $sql)) {
        $response['success'] = true;
        $response['message'] = "Product added successfully.";
    } else {
        $response['success'] = false;
        $response['message'] = "Database error: " . mysqli_error($conn);
    }

} else {
    $response['success'] = false;
    $response['message'] = "Invalid request method.";
}

echo json_encode($response);
?>
