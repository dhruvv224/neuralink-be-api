<?php
include 'config.php';

$id = $_POST['id'] ?? '';
if (empty($id)) {
    echo json_encode(["success" => false, "message" => "ID is required"]);
    exit;
}

$stmt = $conn->prepare("UPDATE category SET isActive=1 WHERE id=?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Category deleted"]);
} else {
    echo json_encode(["success" => false, "message" => "Delete failed"]);
}
