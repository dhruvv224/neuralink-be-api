<?php
$host = "localhost";
$user = "root"; // change if needed
$pass = "";     // change if needed
$db   = "neuralinkproducts"; // your database name

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "DB Connection Failed"]));
}
?>
