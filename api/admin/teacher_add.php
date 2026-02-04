<?php
require_once "../config.php";
require_once "../api_guard.php";

header("Content-Type: application/json");

$employee_id = trim($_POST['employee_id'] ?? '');
$name        = trim($_POST['full_name'] ?? '');
$email       = trim($_POST['email'] ?? '');
$mobile      = trim($_POST['mobile_no'] ?? '');

if ($employee_id==='' || $name==='' || $email==='' || $mobile==='') {
    echo json_encode(["status"=>false,"message"=>"All fields are required"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status"=>false,"message"=>"Invalid email"]);
    exit;
}

/* check duplicate employee_id */
$checkEmp = $conn->prepare(
    "SELECT employee_id FROM teachers WHERE employee_id=?"
);
$checkEmp->bind_param("s", $employee_id);
$checkEmp->execute();
$checkEmp->store_result();

if ($checkEmp->num_rows > 0) {
    echo json_encode(["status"=>false,"message"=>"Employee ID already exists"]);
    exit;
}

/* check duplicate email */
$checkEmail = $conn->prepare(
    "SELECT user_id FROM users WHERE email=?"
);
$checkEmail->bind_param("s", $email);
$checkEmail->execute();
$checkEmail->store_result();

if ($checkEmail->num_rows > 0) {
    echo json_encode(["status"=>false,"message"=>"Email already exists"]);
    exit;
}

/* insert into users (NO PASSWORD, first login forced) */
$stmt = $conn->prepare(
    "INSERT INTO users (email, role, status, first_login)
     VALUES (?, 'teacher', 'approved', 1)"
);
$stmt->bind_param("s", $email);
$stmt->execute();

$user_id = $stmt->insert_id;

/* insert into teachers */
$stmt = $conn->prepare(
    "INSERT INTO teachers (employee_id, user_id, full_name, mobile_no)
     VALUES (?, ?, ?, ?)"
);
$stmt->bind_param(
    "siss",
    $employee_id,
    $user_id,
    $name,
    $mobile
);
$stmt->execute();

echo json_encode([
    "status"=>true,
    "message"=>"Teacher added successfully. Teacher will set password on first login"
]);