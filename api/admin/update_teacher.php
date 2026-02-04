<?php
require_once "../config.php";
require_once "../api_guard.php";

header("Content-Type: application/json");

// ---------- INPUT ----------
$user_id     = intval($_POST['user_id'] ?? 0);
$full_name   = trim($_POST['full_name'] ?? '');
$email       = trim($_POST['email'] ?? '');
$mobile_no   = trim($_POST['mobile_no'] ?? '');
$employee_id = trim($_POST['employee_id'] ?? '');

// ---------- VALIDATION ----------
if ($user_id <= 0 || $full_name === '' || $email === '' || $mobile_no === '') {
    echo json_encode([
        "status" => false,
        "message" => "Invalid input"
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

// ---------- CHECK USER EXISTS ----------
$check = $conn->prepare("SELECT user_id FROM users WHERE user_id=? AND role='teacher'");
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

// ---------- UPDATE USERS TABLE ----------
$u = $conn->prepare("UPDATE users SET email=? WHERE user_id=?");
$u->bind_param("si", $email, $user_id);
$u->execute();

// ---------- UPDATE TEACHERS TABLE ----------
if ($employee_id !== '') {
    $t = $conn->prepare(
        "UPDATE teachers 
         SET full_name=?, mobile_no=?, employee_id=?
         WHERE user_id=?"
    );
    $t->bind_param("sssi", $full_name, $mobile_no, $employee_id, $user_id);
} else {
    $t = $conn->prepare(
        "UPDATE teachers 
         SET full_name=?, mobile_no=?
         WHERE user_id=?"
    );
    $t->bind_param("ssi", $full_name, $mobile_no, $user_id);
}

$t->execute();

// ---------- RESPONSE ----------
echo json_encode([
    "status" => true,
    "message" => "Teacher updated successfully"
]);
