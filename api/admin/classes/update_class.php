<?php
require_once("../../config.php");
require_once("../../api_guard.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

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