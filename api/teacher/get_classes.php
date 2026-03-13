<?php
//get_classes.php
require_once("../config.php");
require_once("../api_guard.php");

header("Content-Type: application/json");

$teacherUserId = $currentUserId;


$stmt = $conn->prepare("
    SELECT DISTINCT class 
    FROM teacher_assignments
    WHERE user_id = ?
");

$stmt->bind_param("i", $teacherUserId);
$stmt->execute();
$result = $stmt->get_result();

$classes = [];

while ($row = $result->fetch_assoc()) {
    $classes[] = $row['class'];
}

echo json_encode([
    "status" => true,
    "classes" => $classes
]);