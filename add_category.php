<?php
include 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

$response = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';

    if (empty($name) || empty($description)) {
        $response['success'] = false;
        $response['message'] = "Name and description are required.";
        echo json_encode($response);
        exit;
    }

    // Create upload folder if not exists
    $uploadDir = __DIR__ . "/uploads/";
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $photoUrl = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $newName = 'cat_' . uniqid() . '.' . $ext;
        $targetFile = $uploadDir . $newName;

        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
            die(json_encode(['success' => false, 'message' => 'File upload failed']));
        }

        $photoUrl = "http://{$_SERVER['HTTP_HOST']}/neuralink/uploads/" . $newName;
    }

    // Escape variables
    $name = mysqli_real_escape_string($conn, $name);
    $description = mysqli_real_escape_string($conn, $description);
    $photoUrl = mysqli_real_escape_string($conn, $photoUrl);

    // Insert into DB
    $sql = "INSERT INTO category (name, photo, description, isActive) 
            VALUES ('$name', '$photoUrl', '$description', 0)";
    if (!mysqli_query($conn, $sql)) {
        die(json_encode(['success' => false, 'message' => 'DB Error: ' . mysqli_error($conn)]));
    }

    $response['success'] = true;
    $response['message'] = "Category added successfully.";
    $response['id'] = mysqli_insert_id($conn);
} else {
    $response['success'] = false;
    $response['message'] = "Invalid request method.";
}

echo json_encode($response);
?>
