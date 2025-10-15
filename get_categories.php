<?php
include 'config.php';

$result = $conn->query("SELECT * FROM category WHERE isActive = 0");
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
echo json_encode(["success" => true, "data" => $data]);
