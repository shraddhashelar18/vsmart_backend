<?php
require_once("../config.php");
require_once("../api_guard.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$class = $data['class'] ?? '';
$subject = $data['subject'] ?? '';
$examType = $data['exam_type'] ?? '';
$totalMarks = $data['total_marks'] ?? '';
$marksList = $data['marks'] ?? [];

if (empty($class) || empty($subject) || empty($examType) || empty($totalMarks)) {
    echo json_encode([
        "status" => false,
        "message" => "Class, Subject, Exam Type and Total Marks are required"
    ]);
    exit;
}

if (empty($marksList)) {
    echo json_encode([
        "status" => false,
        "message" => "No marks data received"
    ]);
    exit;
}

foreach ($marksList as $item) {

    $studentUserId = $item['user_id'];
    $obtainedMarks = $item['obtained_marks'];
    $semester = $item['semester'];

    if ($obtainedMarks > 30) {
        echo json_encode([
            "status" => false,
            "message" => "Marks cannot exceed 30"
        ]);
        exit;
    }

    /* 🔥 Check if already exists */
    $check = $conn->prepare("
        SELECT id FROM marks
        WHERE student_id=? AND subject=? AND exam_type=?
    ");
    $check->bind_param("iss", $studentUserId, $subject, $examType);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {

        /* UPDATE */
        $update = $conn->prepare("
            UPDATE marks
            SET obtained_marks=?, total_marks=?, semester=?
            WHERE student_id=? AND subject=? AND exam_type=?
        ");

        $update->bind_param(
            "iisiss",
            $obtainedMarks,
            $totalMarks,
            $semester,
            $studentUserId,
            $subject,
            $examType
        );

        $update->execute();

    } else {

        /* INSERT */
        $insert = $conn->prepare("
            INSERT INTO marks
            (student_id, teacher_user_id, class, subject, exam_type, total_marks, obtained_marks, semester)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $insert->bind_param(
            "iisssiis",
            $studentUserId,
            $currentUserId,
            $class,
            $subject,
            $examType,
            $totalMarks,
            $obtainedMarks,
            $semester
        );

        $insert->execute();
    }
}

echo json_encode([
    "status" => true,
    "message" => "Marks saved successfully"
]);