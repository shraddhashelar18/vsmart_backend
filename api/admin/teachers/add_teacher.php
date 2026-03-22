<?php
//add teacher.php
require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../api_guard.php");
require_once(__DIR__ . "/../../cors.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");
if($currentRole != "admin"){
    echo json_encode(["status"=>false,"message"=>"Access denied"]);
    exit;
}
$raw = file_get_contents("php://input");

if (!$raw || empty($raw)) {
    $data = $_POST;
} else {
    $data = json_decode($raw, true);
}

if (!$data) {
    echo json_encode([
        "status" => false,
        "message" => "No data received"
    ]);
    exit;
}


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
if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    echo json_encode([
        "status"=>false,
        "message"=>"Invalid email format"
    ]);
    exit;
}

if(strlen($password) < 6){
    echo json_encode([
        "status"=>false,
        "message"=>"Password must be at least 6 characters"
    ]);
    exit;
}

/* ===========================
   CHECK IF EMAIL EXISTS
=========================== */

$check = $conn->prepare("SELECT user_id FROM users WHERE email=?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
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

if (!$stmt->execute()) {
    echo json_encode([
        "status" => false,
        "error" => $stmt->error
    ]);
    exit;
}

$user_id = $stmt->insert_id;
/* ===========================
   INSERT INTO TEACHERS TABLE
=========================== */

$employee_id = $data['employee_id'] ?? '';

$stmt2 = $conn->prepare("
    INSERT INTO teachers (user_id, employee_id, full_name, mobile_no)
    VALUES (?, ?, ?, ?)
");

$stmt2->bind_param("isss", $user_id, $employee_id, $name, $phone);
if (!$stmt2->execute()) {
    echo json_encode([
        "status" => false,
        "error" => $stmt2->error
    ]);
    exit;
}

/* ===========================
   INSERT TEACHER ASSIGNMENTS
=========================== */

/* =========================
   SAVE SUBJECT ASSIGNMENTS
========================= */
if (isset($data['subjects']) && is_array($data['subjects'])) {

    foreach ($data['subjects'] as $department => $classes) {

        foreach ($classes as $className => $subjectList) {

            if (!is_array($subjectList))
                continue;

            foreach ($subjectList as $subject) {

                $stmt = $conn->prepare("
                    INSERT INTO teacher_assignments
(user_id, department, class, subject, status)
VALUES (?,?,?,?, 'active')
                ");

                $stmt->bind_param(
                    "isss",
                    $user_id,
                    $department,
                    $className,
                    $subject
                );

                $stmt->execute();
            }
        }
    }
}
echo json_encode([
    "status" => true,
    "message" => "Teacher added successfully"
]);