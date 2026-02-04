<?php
require_once "../config.php";
require_once "../api_guard.php";

header("Content-Type: application/json");

$user_id = $_POST['user_id'] ?? '';

if ($user_id === '') {
    echo json_encode([
        "status" => false,
        "message" => "User ID is required"
    ]);
    exit;
}

// Delete from students
$stmt = $conn->prepare("DELETE FROM students WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

// Delete from users
$stmt = $conn->prepare(
    "DELETE FROM users WHERE user_id = ? AND role = 'student'"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();

echo json_encode([
    "status" => true,
    "message" => "Student deleted successfully from users and students"
]);
