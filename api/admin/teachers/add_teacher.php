<?php

require_once(__DIR__ . "/../config.php");
require_once(__DIR__ . "/../api_guard.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

/* ===========================
   VALIDATE INPUT
=========================== */

$name = $data['name'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$phone = $data['phone'] ?? '';
$departments = $data['departments'] ?? [];
$subjects = $data['subjects'] ?? [];

if (empty($name) || empty($email) || empty($password)) {
    echo json_encode([
        "status" => false,
        "message" => "Required fields missing"
    ]);
    exit;
}

/* ===========================
   CHECK IF EMAIL EXISTS
=========================== */

$check = $conn->prepare("SELECT user_id FROM users WHERE email=?");
$check->bind_param("s", $email);
$check->execute();
$res = $check->get_result();

if ($res->num_rows > 0) {
    echo json_encode([
        "status" => false,
        "message" => "Email already exists"
    ]);
    exit;
}

/* ===========================
   INSERT INTO USERS TABLE
=========================== */

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("
    INSERT INTO users (email, password, role, status)
    VALUES (?, ?, 'teacher', 'approved')
");

$stmt->bind_param("ss", $email, $hashedPassword);
$stmt->execute();

$user_id = $stmt->insert_id;

/* ===========================
   INSERT INTO TEACHERS TABLE
=========================== */

$employee_id = rand(100000, 999999);

$stmt2 = $conn->prepare("
    INSERT INTO teachers (user_id, employee_id, full_name, mobile_no)
    VALUES (?, ?, ?, ?)
");

$stmt2->bind_param("iiss", $user_id, $employee_id, $name, $phone);
$stmt2->execute();

/* ===========================
   INSERT TEACHER ASSIGNMENTS
=========================== */

foreach ($subjects as $class => $subjectList) {

    foreach ($subjectList as $subject) {

        $department_code = substr($class, 0, 2); // IF, CO, EJ

        $stmt3 = $conn->prepare("
            INSERT INTO teacher_assignments
            (user_id, department_code, class, subject, status)
            VALUES (?, ?, ?, ?, 'active')
        ");

        $stmt3->bind_param("isss", $user_id, $department_code, $class, $subject);
        $stmt3->execute();
    }
}

echo json_encode([
    "status" => true,
    "message" => "Teacher added successfully"
]);