<?php
require_once "../config.php";
require_once "../api_guard.php";

header("Content-Type: application/json");

$class = $_POST['class'] ?? '';

if ($class === '') {
    echo json_encode([
        "status" => false,
        "message" => "Class is required"
    ]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT 
        s.user_id,
        s.full_name,
        u.email,
        s.mobile_no,
        s.class,
        s.parent_mobile_no
     FROM students s
     JOIN users u ON u.user_id = s.user_id
     WHERE s.class = ?"
);

$stmt->bind_param("s", $class);
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