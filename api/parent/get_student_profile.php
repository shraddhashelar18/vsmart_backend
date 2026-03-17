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

$query = $conn->prepare("SELECT * FROM students WHERE enrollment_no=?");
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
    "phone" => $student['mobile_no']
]);
?>