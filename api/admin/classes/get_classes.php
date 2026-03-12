<?php

require_once("../../config.php");

header("Content-Type: application/json");

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