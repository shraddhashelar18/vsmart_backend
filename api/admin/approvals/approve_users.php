<?php
require_once "../../config.php";
require_once "../../api_guard.php";

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "status" => false,
        "message" => "Method not allowed"
    ]);
    exit;
}

$user_id = $_POST['user_id'] ?? '';
$role    = $_POST['role'] ?? '';

if ($user_id === '' || $role === '') {
    echo json_encode([
        "status" => false,
        "message" => "user_id and role are required"
    ]);
    exit;
}

/* ---------- TEACHER EXTRA DATA BEFORE APPROVAL ---------- */
if ($role === 'teacher') {

    $department = $_POST['department'] ?? '';
    $class      = $_POST['class'] ?? '';

    if ($department === '' || $class === '') {
        echo json_encode([
            "status" => false,
            "message" => "Department and class are required for teacher approval"
        ]);
        exit;
    }

    $stmt = $conn->prepare(
        "UPDATE teachers
         SET department = ?, class = ?
         WHERE user_id = ?"
    );
    $stmt->bind_param("ssi", $department, $class, $user_id);
    $stmt->execute();
}

/* ---------- APPROVE USER ---------- */
$stmt = $conn->prepare(
    "UPDATE users
     SET status = 'approved'
     WHERE user_id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();

echo json_encode([
    "status" => true,
    "message" => ucfirst($role) . " approved successfully"
]);