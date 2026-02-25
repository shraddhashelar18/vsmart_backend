<?php
require_once("../config.php");

header("Content-Type: application/json");

/* ================= API KEY CHECK ================= */

$headers = getallheaders();

if (!isset($headers['x-api-key']) || $headers['x-api-key'] != "VSMART_API_2026") {
    echo json_encode([
        "status" => false,
        "message" => "Invalid API Key"
    ]);
    exit;
}

/* ================= GET INPUT ================= */

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['email']) || !isset($data['password'])) {
    echo json_encode([
        "status" => false,
        "message" => "Email and Password required"
    ]);
    exit;
}

$email = $data['email'];
$password = $data['password'];

/* ================= CHECK USER ================= */

$stmt = $conn->prepare("
    SELECT user_id, email, password, role, status
    FROM users
    WHERE email = ?
");

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode([
        "status" => false,
        "message" => "User not found"
    ]);
    exit;
}

$user = $result->fetch_assoc();

/* ================= CHECK APPROVAL ================= */

if ($user['status'] != "approved") {
    echo json_encode([
        "status" => false,
        "message" => "Account not approved"
    ]);
    exit;
}

/* ================= CHECK PASSWORD ================= */

if (!password_verify($password, $user['password'])) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid password"
    ]);
    exit;
}

/* ================= GENERATE AUTH TOKEN ================= */

$auth_token = bin2hex(random_bytes(32));

$updateToken = $conn->prepare("
    UPDATE users
    SET auth_token = ?
    WHERE user_id = ?
");

$updateToken->bind_param("si", $auth_token, $user['user_id']);
$updateToken->execute();

/* ================= ROLE BASED DATA ================= */

$department = null;
$principalName = null;

/* ---------- HOD ---------- */
if ($user['role'] == "hod") {

    $stmt = $conn->prepare("
        SELECT department
        FROM hod
        WHERE user_id = ?
    ");

    $stmt->bind_param("i", $user['user_id']);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $rowDept = $res->fetch_assoc();
        $department = $rowDept['department'];
    }
}

/* ---------- PRINCIPAL ---------- */
if ($user['role'] == "principal") {

    $stmt = $conn->prepare("
        SELECT full_name
        FROM principal
        WHERE user_id = ?
    ");

    $stmt->bind_param("i", $user['user_id']);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $rowPrincipal = $res->fetch_assoc();
        $principalName = $rowPrincipal['full_name'];
    }
}

/* ---------- TEACHER ---------- */
$teacherName = null;

if ($user['role'] == "teacher") {

    $stmt = $conn->prepare("
        SELECT full_name
        FROM teachers
        WHERE user_id = ?
    ");

    $stmt->bind_param("i", $user['user_id']);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $rowTeacher = $res->fetch_assoc();
        $teacherName = $rowTeacher['full_name'];
    }
}

/* ---------- STUDENT ---------- */
$studentName = null;

if ($user['role'] == "student") {

    $stmt = $conn->prepare("
        SELECT full_name, department_code
        FROM students
        WHERE user_id = ?
    ");

    $stmt->bind_param("i", $user['user_id']);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $rowStudent = $res->fetch_assoc();
        $studentName = $rowStudent['full_name'];
        $department = $rowStudent['department_code'];
    }
}

/* ================= SUCCESS RESPONSE ================= */

echo json_encode([
    "status" => true,
    "message" => "Login successful",
    "auth_token" => $auth_token,
    "user" => [
        "user_id" => $user['user_id'],
        "email" => $user['email'],
        "role" => $user['role'],
        "department" => $department,
        "principal_name" => $principalName,
        "teacher_name" => $teacherName,
        "student_name" => $studentName
    ]
]);