<?php
//get_student_profile.php

require_once "../config.php";
require_once "../cors.php"; 
require_once "../api_guard.php"; // ✅ ADDED

header("Content-Type: application/json");

/* ================= ROLE CHECK ================= */
if ($currentRole != 'parent') {
    echo json_encode([
        "status" => false,
        "message" => "Access Denied"
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['enrollment'])) {
    echo json_encode(["status" => false, "message" => "enrollment required"]);
    exit;
}

$enrollment = $data['enrollment'];

$query = $conn->prepare("
SELECT 
    s.enrollment_no,
    s.full_name,
    s.roll_no,
    s.class,
    s.mobile_no,
    u.email
FROM students s
JOIN users u ON s.user_id = u.user_id
WHERE s.enrollment_no=?
");

$query->bind_param("s", $enrollment);
$query->execute();
$result = $query->get_result();

if ($result->num_rows == 0) {
    echo json_encode(["status" => false]);
    exit;
}

$student = $result->fetch_assoc();

echo json_encode([
    "status" => true,
    "enrollment" => $student['enrollment_no'],
    "name" => $student['full_name'],
    "roll" => $student['roll_no'],
    "class" => $student['class'],
    "phone" => $student['mobile_no'],
    "email" => $student['email']   
]);
?>