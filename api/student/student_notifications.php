<?php
require_once "../config.php";
require_once "../api_guard.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$student_id = intval($data['student_id'] ?? 0);

if ($student_id <= 0) {
    echo json_encode([
        "status" => false,
        "message" => "Student required"
    ]);
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        n.class,
        n.subject_name,   -- use subject_id because your table has this column
        n.message,
        n.created_at
    FROM notifications n
    INNER JOIN notification_receivers r 
        ON r.notification_id = n.id
    WHERE r.student_id = ?
    ORDER BY n.created_at DESC
");

$stmt->bind_param("i", $student_id);
$stmt->execute();

$result = $stmt->get_result();

echo json_encode([
    "status" => true,
    "notifications" => $result->fetch_all(MYSQLI_ASSOC)
]);
