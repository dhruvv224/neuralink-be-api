<?php
include 'config.php';

header('Content-Type: application/json');

$cat_id = $_GET['cat_id'] ?? '';

$sql = "SELECT id, cat_id, name, short_description, product_photo, description
        FROM product 
        WHERE isActive = 0";

if (!empty($cat_id)) {
    $sql .= " AND cat_id = " . intval($cat_id);
}

$result = $conn->query($sql);
$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode([
    "success" => true,
    "data" => $data
]);
?>
