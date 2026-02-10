<?php
require_once "../config.php";
require_once "../api_guard.php";

header("Content-Type: application/json");

// Read JSON body
$data = json_decode(file_get_contents("php://input"), true);

// Collect inputs
$class           = $data['class'] ?? '';
$subject         = $data['subject'] ?? '';
$exam_type       = $data['exam_type'] ?? '';
$teacher_user_id = $data['teacher_user_id'] ?? '';
$total_marks     = $data['total_marks'] ?? '';
$marks_list      = $data['marks'] ?? [];

if (
    $class === '' || 
    $subject === '' || 
    $exam_type === '' || 
    $teacher_user_id === '' || 
    $total_marks === ''
) {
    echo json_encode([
        "status" => false,
        "message" => "Missing required fields"
    ]);
    exit;
}

// Prepare insert
$stmt = $conn->prepare(
    "INSERT INTO marks
     (student_id, teacher_user_id, class, subject, exam_type, total_marks, obtained_marks)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);

// Insert marks for each student
foreach ($marks_list as $row) {

    $student_user_id = $row['student_user_id'];
    $obtained_marks  = $row['obtained_marks'];

    $stmt->bind_param(
        "iisssii",
        $student_user_id,
        $teacher_user_id,
        $class,
        $subject,
        $exam_type,
        $total_marks,
        $obtained_marks
    );

    $stmt->execute();
}

echo json_encode([
    "status" => true,
    "message" => "Marks submitted successfully"
]);