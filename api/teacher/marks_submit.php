<?php
require_once "../config.php";
require_once "../api_guard.php";

header("Content-Type: application/json");

// Read JSON body
$data = json_decode(file_get_contents("php://input"), true);

// Collect inputs
$class            = $data['class'] ?? '';
$subject          = $data['subject'] ?? '';
$exam_type        = $data['exam_type'] ?? '';
$teacher_user_id  = $data['teacher_user_id'] ?? '';
$marks_list       = $data['marks'] ?? [];

if ($class === '' || $subject === '' || $exam_type === '' || $teacher_user_id === '') {
    echo json_encode([
        "status" => false,
        "message" => "Missing required fields"
    ]);
    exit;
}

// Prepare insert
$stmt = $conn->prepare(
    "INSERT INTO marks
     (student_id, teacher_user_id, class, subject, exam_type, marks)
     VALUES (?, ?, ?, ?, ?, ?)"
);

// Insert marks for each student
foreach ($marks_list as $row) {

    $student_id = $row['student_user_id'];
    $mark       = $row['marks'];

    $stmt->bind_param(
        "iisssi",
        $student_id,
        $teacher_user_id,
        $class,
        $subject,
        $exam_type,
        $mark
    );

    $stmt->execute();
}

echo json_encode([
    "status" => true,
    "message" => "Marks submitted successfully"
]);