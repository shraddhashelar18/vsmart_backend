<?php
require_once("../../config.php");
require_once("../../api_guard.php");
require_once("../../cors.php");


header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

/* VALIDATION */
if (empty($data['class_name']) || empty($data['department']) || empty($data['class_teacher'])) {
    echo json_encode([
        "status"=>false,
        "message"=>"All fields required"
    ]);
    exit;
}

if (!is_numeric($data['class_teacher'])) {
    echo json_encode([
        "status"=>false,
        "message"=>"Invalid class teacher ID"
    ]);
    exit;
}

/* Check Duplicate Class */
$check = $conn->prepare("SELECT class_id FROM classes WHERE class_name=?");
$check->bind_param("s", $data['class_name']);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode([
        "status"=>false,
        "message"=>"Class already exists"
    ]);
    exit;
}

/* Extract semester */
preg_match('/\d+/', $data['class_name'], $match);
$semester = $match[0] ?? 1;

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