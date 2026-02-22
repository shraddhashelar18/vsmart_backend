<?php
require_once "../config.php";
require_once "../api_guard.php";

header("Content-Type: application/json");

$student_id = $_POST['student_id'] ?? '';

if ($student_id === '') {
    echo json_encode([
        "status" => false,
        "message" => "Student required"
    ]);
    exit;
}

$stmt = $conn->prepare("
    SELECT n.class, n.subject, n.message, n.created_at
    FROM notifications n
    JOIN notification_receivers r
      ON r.notification_id = n.id
    WHERE r.student_id = ?
    ORDER BY n.created_at DESC
");
$stmt->bind_param("i", $student_id);
$stmt->execute();

echo json_encode([
    "status" => true,
    "notifications" => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)
]);