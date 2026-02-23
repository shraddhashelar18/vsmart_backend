<?php
require_once "../config.php";
require_once "../api_guard.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$class           = $data['class'] ?? '';
$subject_name         = $data['subject_name'] ?? '';
$message         = trim($data['message'] ?? '');
$teacher_user_id = intval($data['teacher_user_id'] ?? 0);
$students        = $data['students_id'] ?? [];   // ✅ FIXED

if (
    $class === '' ||
    $subject_name === '' ||
    $message === '' ||
    $teacher_user_id <= 0 ||
    empty($students)
) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid input"
    ]);
    exit;
}

/* Insert notification */
$stmt = $conn->prepare(
    "INSERT INTO notifications (teacher_user_id, class, subject_name, message)
     VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("isss", $teacher_user_id, $class, $subject_name, $message);
$stmt->execute();

$notification_id = $stmt->insert_id;

/* Insert receivers */
$stmt2 = $conn->prepare(
    "INSERT INTO notification_receivers (notification_id, student_id)
     VALUES (?, ?)"
);

foreach ($students as $student_id) {
    $stmt2->bind_param("ii", $notification_id, $student_id);
    $stmt2->execute();
}

echo json_encode([
    "status" => true,
    "message" => "Notification sent successfully"
]);
