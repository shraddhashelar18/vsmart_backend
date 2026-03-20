<?php
//mark_attendance.php
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
preg_match('/\d+/', $class, $match);
$semester = $match[0];
$subject = trim($data['subject'] ?? '');
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
    // ✅ allow only valid attendance status
    if (!in_array($status, ['P','A','L'])) {
        continue;
    }

    $stmt = $conn->prepare("
    INSERT INTO attendance
    (student_id, teacher_id, class, semester, subject_name, date, status)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "iisssss",
    $studentUserId,
    $currentUserId,
    $class,
    $semester,
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