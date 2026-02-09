<?php
require_once "../config.php";
require_once "../api_guard.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $conn->prepare("
    INSERT INTO attendance
    (student_id, teacher_user_id, class, subject, date, status)
    VALUES (?, ?, ?, ?, ?, ?)
");

foreach ($data['attendance'] as $a) {
    $stmt->bind_param(
        "iissss",
        $a['student_user_id'],
        $data['teacher_user_id'],
        $data['class'],
        $data['subject'],
        $data['date'],
        $a['status']
    );
    $stmt->execute();
}

echo json_encode(["status"=>true,"message"=>"Attendance saved"]);