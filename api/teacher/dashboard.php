<?php
require_once "../config.php";
require_once "../api_guard.php";
header("Content-Type: application/json");

$user_id = $_POST['user_id'] ?? '';

$stmt = $conn->prepare("
    SELECT full_name, employee_id
    FROM teachers
    WHERE user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("
    SELECT department_code, class, subject
    FROM teacher_assignments
    WHERE user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$assignments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    "status"=>true,
    "teacher"=>$teacher,
    "assignments"=>$assignments
]);