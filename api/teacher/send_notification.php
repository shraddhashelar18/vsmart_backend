<?php
// send_notification.php

require_once("../config.php");
require_once("../api_guard.php");
require_once("../cors.php");
header("Content-Type: application/json");
if($currentRole != "teacher"){
    echo json_encode([
        "status" => false,
        "message" => "Access denied"
    ]);
    exit;
}
$data = json_decode(file_get_contents("php://input"), true);

$class = $data['class'] ?? '';
$subject = trim($data['subject'] ?? '');
$message = trim($data['message'] ?? '');
$sendTo = $data['send_to'] ?? '';
$selectedRecipients = $data['students'] ?? [];

if (empty($class) || empty($subject) || empty($message) || empty($sendTo)) {
    echo json_encode([
        "status" => false,
        "message" => "All fields are required"
    ]);
    exit;
}

/* Insert Notification */

$created_at = date("Y-m-d H:i:s");

$stmt = $conn->prepare("
    INSERT INTO notifications
    (teacher_user_id, class, subject, message, created_at)
    VALUES (?, ?, ?, ?, ?)
");

$stmt->bind_param("issss", $currentUserId, $class, $subject, $message, $created_at);
$stmt->execute();

$notificationId = $stmt->insert_id;


/* =============================
   SEND TO WHOLE STUDENTS
============================= */

if ($sendTo == "wholeStudents") {

    $studentQuery = $conn->prepare("
        SELECT user_id
        FROM students
        WHERE class=?
    ");

    $studentQuery->bind_param("s", $class);
    $studentQuery->execute();
    $result = $studentQuery->get_result();

    while ($row = $result->fetch_assoc()) {
        if (!empty($row['user_id'])) {
            insertReceiver($conn, $notificationId, $row['user_id']);
        }
    }
}


/* =============================
   SEND TO SELECTED STUDENTS
============================= */

elseif ($sendTo == "selectedStudents") {

    if (empty($selectedRecipients)) {
        echo json_encode([
            "status" => false,
            "message" => "Select at least one student"
        ]);
        exit;
    }

    foreach ($selectedRecipients as $studentUserId) {
        insertReceiver($conn, $notificationId, $studentUserId);
    }
}


/* =============================
   SEND TO WHOLE PARENTS
============================= */

elseif ($sendTo == "wholeParents") {

    $parentQuery = $conn->prepare("
        SELECT DISTINCT p.user_id
        FROM students s
        JOIN parents p ON s.enrollment_no = p.enrollment_no
        WHERE s.class=?
    ");

    $parentQuery->bind_param("s", $class);
    $parentQuery->execute();
    $result = $parentQuery->get_result();

    while ($row = $result->fetch_assoc()) {
        if (!empty($row['user_id'])) {
            insertReceiver($conn, $notificationId, $row['user_id']);
        }
    }
}


/* =============================
   SEND TO SELECTED PARENTS
============================= */

elseif ($sendTo == "selectedParents") {

    if (empty($selectedRecipients)) {
        echo json_encode([
            "status" => false,
            "message" => "Select at least one parent"
        ]);
        exit;
    }

    foreach ($selectedRecipients as $parentUserId) {
        insertReceiver($conn, $notificationId, $parentUserId);
    }
}


/* RESPONSE */

echo json_encode([
    "status" => true,
    "message" => "Notification sent successfully"
]);


/* =============================
   INSERT RECEIVER FUNCTION
============================= */

function insertReceiver($conn, $notificationId, $userId) {

    $stmt = $conn->prepare("
        INSERT INTO notification_receivers
        (notification_id, receiver_user_id)
        VALUES (?, ?)
    ");

    $stmt->bind_param("ii", $notificationId, $userId);
    $stmt->execute();
}
?>