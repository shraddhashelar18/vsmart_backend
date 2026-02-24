<?php
require_once("../../config.php");
require_once("../../api_guard.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

/* Extract semester from class_name */
preg_match('/\d+/', $data['class_name'], $match);
$semester = $match[0];

$stmt = $conn->prepare("
INSERT INTO classes (class_name, department, semester, class_teacher)
VALUES (?,?,?,?)
");

$stmt->bind_param(
    "ssii",
    $data['class_name'],
    $data['department'],
    $semester,
    $data['class_teacher']
);

$stmt->execute();

echo json_encode(["status"=>true]);
