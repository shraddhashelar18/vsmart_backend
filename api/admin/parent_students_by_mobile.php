<?php
require_once "../config.php";
require_once "../api_guard.php";

header("Content-Type: application/json");

/* READ POST DATA */
$mobile = trim($_POST['mobile_no'] ?? '');

if ($mobile === '') {
    echo json_encode([
        "status" => false,
        "message" => "Parent mobile number is required"
    ]);
    exit;
}

/* FETCH ALL STUDENTS WITH SAME PARENT MOBILE */
$stmt = $conn->prepare(
    "SELECT 
        user_id,
        full_name,
        roll_no,
        class
     FROM students
     WHERE parent_mobile_no = ?"
);

$stmt->bind_param("s", $mobile);
$stmt->execute();
$result = $stmt->get_result();

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

echo json_encode([
    "status" => true,
    "count" => count($students),
    "students" => $students
]);