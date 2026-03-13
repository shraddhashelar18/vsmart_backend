<?php

require_once("../../config.php");
require_once("../../api_guard.php");
require_once("../../cors.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$enrollment = $data['enrollment'] ?? '';

if (empty($enrollment)) {
    echo json_encode([
        "status" => false,
        "message" => "Enrollment required"
    ]);
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        s.user_id,
        s.enrollment_no AS enrollment,
        s.full_name AS name,
        u.email,
        s.mobile_no AS phone,
        s.parent_mobile_no AS parentPhone,
        s.roll_no AS roll,
        s.class
    FROM students s
    LEFT JOIN users u ON u.user_id = s.user_id
    WHERE s.enrollment_no = ?
");

$stmt->bind_param("s", $enrollment);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode([
        "status" => false,
        "message" => "Student not found"
    ]);
    exit;
}

$student = $result->fetch_assoc();

echo json_encode([
    "status" => true,
    ...$student
]);