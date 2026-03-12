<?php
require_once("../config.php");
require_once("../api_guard.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$class = $data['class'] ?? '';
$subject = $data['subject'] ?? '';
$message = trim($data['message'] ?? '');
$sendTo = $data['send_to'] ?? '';
$selectedStudents = $data['students'] ?? [];

if (empty($class) || empty($subject) || empty($message) || empty($sendTo)) {
    echo json_encode([
        "status" => false,
        "message" => "All fields are required"
    ]);
    exit;
}

if ($sendTo == "Students" && empty($selectedStudents)) {
    echo json_encode([
        "status" => false,
        "message" => "Select at least one student"
    ]);
    exit;
}

$title = $subject . " - Notification";

/* Insert Notification */
$created_at = date("Y-m-d H:i:s");
$stmt = $conn->prepare("
    INSERT INTO notifications
    (teacher_user_id, class, subject, message, created_at)
    VALUES (?, ?, ?, ?, ?)
");

$stmt->bind_param("issss", $currentUserId, $class, $subject,$message,$created_at);
$stmt->execute();

$notificationId = $stmt->insert_id;

/* Send To Students */
if ($sendTo == "Whole Class") {

    $studentQuery = $conn->prepare("
        SELECT user_id FROM students WHERE class=?
    ");
    $studentQuery->bind_param("s", $class);
    $studentQuery->execute();
    $result = $studentQuery->get_result();

    while ($row = $result->fetch_assoc()) {
        if(!empty($row['user_id'])){
   insertReceiver($conn, $notificationId, $row['user_id']);
}
    }

} else {

    foreach ($selectedStudents as $studentUserId) {
        insertReceiver($conn, $notificationId, $studentUserId);
    }
}

echo json_encode([
    "status" => true,
    "message" => "Notification sent successfully"
]);

function insertReceiver($conn, $notificationId, $userId) {
    $stmt = $conn->prepare("
        INSERT INTO notification_receivers
        (notification_id, student_id)
        VALUES (?, ?)
    ");
    $stmt->bind_param("ii", $notificationId, $userId);
    $stmt->execute();
}
