<?php
require_once("../../config.php");
require_once("../../api_guard.php");
require_once("../../cors.php");
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['class_name']) || empty($data['class_teacher'])) {
    echo json_encode([
        "status"=>false,
        "message"=>"Class name and teacher required"
    ]);
    exit;
}

$check = $conn->prepare("
SELECT class_name 
FROM classes 
WHERE class_teacher=? 
AND class_name!=?
");

$check->bind_param("is", $data['class_teacher'], $data['class_name']);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    echo json_encode([
        "status" => false,
        "message" => "Teacher already assigned to another class"
    ]);
    exit;
}

$stmt = $conn->prepare("
UPDATE classes
SET class_teacher=?
WHERE class_name=?
");

$stmt->bind_param(
    "is",
    $data['class_teacher'],
    $data['class_name']
);

$stmt->execute();

echo json_encode(["status"=>true]);
