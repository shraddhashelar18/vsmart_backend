<?php

require_once("../../config.php");

$data = json_decode(file_get_contents("php://input"), true);

require_once("../../api_guard.php");
require_once("../../cors.php");

header("Content-Type: application/json");
$rawData = file_get_contents("php://input");

if (!$rawData || empty($rawData)) {
    $data = $_POST;
} else {
    $data = json_decode($rawData, true);
}

if (!$data) {
    echo json_encode([
        "status" => false,
        "message" => "No data received"
    ]);
    exit;
}

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

$response = [
    "status" => true
];

foreach ($student as $key => $value) {
    $response[$key] = $value;
}

echo json_encode($response);