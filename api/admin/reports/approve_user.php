<?php
require_once("../../config.php");
require_once("../../api_guard.php");
require_once("../../cors.php");

header("Content-Type: application/json");

if ($currentRole != "admin") {
    echo json_encode(["status" => false, "message" => "Access denied"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $data['user_id'] ?? '';
$class = $data['class'] ?? ''; // 🔥 must send from frontend

if (empty($user_id)) {
    echo json_encode([
        "status" => false,
        "message" => "User id required"
    ]);
    exit;
}

/* =========================
   GET USER BASIC INFO
========================= */

$stmt = $conn->prepare("SELECT email, role FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    echo json_encode(["status" => false, "message" => "User not found"]);
    exit;
}

$user = $res->fetch_assoc();

/* =========================
   EXTRACT CLASS DATA
========================= */

$department = substr($class, 0, 2); // IF

preg_match('/\d+/', $class, $match);
$semester = isset($match[0]) ? (int) $match[0] : 0;

/* =========================
   INSERT INTO ROLE TABLE
========================= */

if ($user['role'] == "student") {

    $stmt = $conn->prepare("
        INSERT INTO students (
            user_id,
            class,
            department,
            current_semester
        ) VALUES (?, ?, ?, ?)
    ");

    $stmt->bind_param("issi", $user_id, $class, $department, $semester);
    $stmt->execute();
}

if ($user['role'] == "teacher") {

    $stmt = $conn->prepare("
        INSERT INTO teachers (
            user_id
        ) VALUES (?)
    ");

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
}

/* =========================
   UPDATE STATUS
========================= */

$stmt = $conn->prepare("UPDATE users SET status='approved' WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

echo json_encode([
    "status" => true,
    "message" => "User approved successfully"
]);