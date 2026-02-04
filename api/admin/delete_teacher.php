<?php
require_once "../config.php";
require_once "../api_guard.php";

header("Content-Type: application/json");

$user_id = intval($_POST['user_id'] ?? 0);

if ($user_id <= 0) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid user_id"
    ]);
    exit;
}

/*
 STEP 1: Check user exists & is teacher
*/
$check = $conn->prepare(
    "SELECT user_id FROM users WHERE user_id=? AND role='teacher'"
);
$check->bind_param("i", $user_id);
$check->execute();
$check->store_result();

if ($check->num_rows === 0) {
    echo json_encode([
        "status" => false,
        "message" => "Teacher not found"
    ]);
    exit;
}

/*
 STEP 2: Delete from teachers table
*/
$stmt = $conn->prepare("DELETE FROM teachers WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

/*
 STEP 3: Delete from users table
*/
$stmt = $conn->prepare("DELETE FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

echo json_encode([
    "status" => true,
    "message" => "Teacher deleted from system"
]);
