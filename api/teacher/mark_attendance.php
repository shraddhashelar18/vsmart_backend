<?php
require_once("../config.php");
require_once("../api_guard.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$class = $data['class'] ?? '';
$subject = $data['subject_name'] ?? '';
$date = $data['date'] ?? '';
$attendanceList = $data['attendance'] ?? [];

if (empty($class) || empty($subject) || empty($date)) {
    echo json_encode([
        "status" => false,
        "message" => "Class, Subject and Date are required"
    ]);
    exit;
}

if (empty($attendanceList)) {
    echo json_encode([
        "status" => false,
        "message" => "No attendance data received"
    ]);
    exit;
}

/* 🔥 Duplicate Check */
$check = $conn->prepare("
    SELECT id FROM attendance
    WHERE class=? AND subject_name=? AND date=?
");
$check->bind_param("sss", $class, $subject, $date);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    echo json_encode([
        "status" => false,
        "message" => "Attendance already marked for this date"
    ]);
    exit;
}

/* Insert Attendance */
foreach ($attendanceList as $item) {

    $studentUserId = $item['user_id'];
    $status = $item['status'];

    if (empty($studentUserId) || empty($status)) continue;

    $stmt = $conn->prepare("
        INSERT INTO attendance
        (student_id, teacher_id, class, subject_name, date, status)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "iissss",
        $studentUserId,
        $currentUserId,
        $class,
        $subject,
        $date,
        $status
    );

    $stmt->execute();
}

echo json_encode([
    "status" => true,
    "message" => "Attendance submitted successfully"
]);