<?php
require_once "../config.php";
require_once "../api_guard.php";

header("Content-Type: application/json");

$class = trim($_POST['class'] ?? '');

if ($class === '') {
    echo json_encode([
        "status" => false,
        "message" => "Class is required"
    ]);
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        user_id,
        full_name,
        roll_no,
        enrollment_no
    FROM students
    WHERE class = ?
    ORDER BY roll_no
");

$stmt->bind_param("s", $class);
$stmt->execute();

$result = $stmt->get_result();

echo json_encode([
    "status" => true,
    "students" => $result->fetch_all(MYSQLI_ASSOC)
]);