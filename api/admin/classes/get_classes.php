<?php
require_once("../../api_guard.php");
require_once("../../config.php");
require_once("../../cors.php");

header("Content-Type: application/json");
if($currentRole != "admin"){
    echo json_encode(["status"=>false,"message"=>"Access denied"]);
    exit;
}

$result = $conn->query("
SELECT class_name
FROM classes
ORDER BY class_name
");

$classes = [];

while ($row = $result->fetch_assoc()) {
    $classes[] = $row['class_name'];
}

echo json_encode([
    "status" => true,
    "classes" => $classes
]);