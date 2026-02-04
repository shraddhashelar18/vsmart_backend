<?php
require_once "../../config.php";
require_once "../../api_guard.php";

header("Content-Type: application/json");

$user_id = $_POST['user_id'] ?? $_GET['user_id'] ?? '';

if ($user_id === '') {
    echo json_encode([
        "status" => false,
        "message" => "User ID is required"
    ]);
    exit;
}

// Check user exists & pending
$check = $conn->prepare(
    "SELECT role, status FROM users WHERE user_id = ?"
);
$check->bind_param("i", $user_id);
$check->execute();
$res = $check->get_result();

if ($res->num_rows === 0) {
    echo json_encode([
        "status" => false,
        "message" => "User not found"
    ]);
    exit;
}

$user = $res->fetch_assoc();

if ($user['status'] === 'approved') {
    echo json_encode([
        "status" => false,
        "message" => "User already approved"
    ]);
    exit;
}

// Approve user
$update = $conn->prepare(
    "UPDATE users SET status = 'approved' WHERE user_id = ?"
);
$update->bind_param("i", $user_id);
$update->execute();

echo json_encode([
    "status" => true,
    "message" => "User approved successfully",
    "user_id" => $user_id,
    "role" => $user['role']
]);
