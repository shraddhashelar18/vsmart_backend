<?php
require_once "../config.php";
require_once "../api_guard.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$class = $data['class'] ?? '';
$subject = $data['subject'] ?? '';
$message = trim($data['message'] ?? '');
$teacher_user_id = $data['teacher_user_id'] ?? '';
$students = $data['students'] ?? [];

if (
    $class === '' ||
    $subject === '' ||
    $message === '' ||
    $teacher_user_id === '' ||
    empty($students)
) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid input"
    ]);
    exit;
}

// Insert notification
$stmt = $conn->prepare(
    "INSERT INTO notifications (teacher_user_id, class, subject, message)
     VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("isss", $teacher_user_id, $class, $subject, $message);
$stmt->execute();

$notification_id = $stmt->insert_id;

// Link selected students
$stmt2 = $conn->prepare(
    "INSERT INTO notification_receivers (notification_id, student_user_id)
     VALUES (?, ?)"
);

foreach ($students as $student_user_id) {
    $stmt2->bind_param("ii", $notification_id, $student_user_id);
    $stmt2->execute();
}

echo json_encode([
    "status" => true,
    "message" => "Notification sent to selected students"
]);