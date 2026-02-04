<?php
require_once "../config.php";
require_once "../api_guard.php";

header("Content-Type: application/json");

// 🔹 READ JSON INPUT
$data = json_decode(file_get_contents("php://input"), true);

$user_id = intval($data['user_id'] ?? 0);
$name    = trim($data['full_name'] ?? '');
$email   = trim($data['email'] ?? '');
$class   = trim($data['class'] ?? '');
$mobile  = trim($data['mobile_no'] ?? '');
$parent  = trim($data['parent_mobile_no'] ?? '');

// 🔹 VALIDATION
if ($user_id <= 0 || $name === '' || $email === '' || $class === '' || $mobile === '') {
    echo json_encode([
        "status" => false,
        "message" => "All required fields must be filled"
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid email format"
    ]);
    exit;
}

// 🔹 CHECK STUDENT EXISTS
$check = $conn->prepare("SELECT user_id FROM students WHERE user_id=?");
$check->bind_param("i", $user_id);
$check->execute();
$check->store_result();

if ($check->num_rows === 0) {
    echo json_encode([
        "status" => false,
        "message" => "Student not found"
    ]);
    exit;
}

// 🔹 UPDATE STUDENTS TABLE
if ($parent !== '') {
    $stmt = $conn->prepare(
        "UPDATE students 
         SET full_name=?, class=?, mobile_no=?, parent_mobile_no=?
         WHERE user_id=?"
    );
    $stmt->bind_param("ssssi", $name, $class, $mobile, $parent, $user_id);
} else {
    $stmt = $conn->prepare(
        "UPDATE students 
         SET full_name=?, class=?, mobile_no=?, parent_mobile_no=NULL
         WHERE user_id=?"
    );
    $stmt->bind_param("sssi", $name, $class, $mobile, $user_id);
}
$stmt->execute();

// 🔹 UPDATE EMAIL IN USERS TABLE
$stmt = $conn->prepare(
    "UPDATE users SET email=? WHERE user_id=?"
);
$stmt->bind_param("si", $email, $user_id);
$stmt->execute();

echo json_encode([
    "status" => true,
    "message" => "Student updated successfully"
]);
