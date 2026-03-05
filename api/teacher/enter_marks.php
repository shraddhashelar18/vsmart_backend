<?php
require_once("../config.php");
require_once("../api_guard.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$class = $data['class'] ?? '';
$subject = $data['subject'] ?? '';
$examType = $data['exam_type'] ?? '';
$totalMarks = $data['total_marks'] ?? 30;
$isDraft = $data['is_draft'] ?? true;
$marksList = $data['marks'] ?? [];

if (empty($class) || empty($subject) || empty($examType)) {
    echo json_encode([
        "status" => false,
        "message" => "Class, Subject and Exam Type required"
    ]);
    exit;
}

if (empty($marksList)) {
    echo json_encode([
        "status" => false,
        "message" => "No marks received"
    ]);
    exit;
}

foreach ($marksList as $item) {

    $studentUserId = $item['user_id'];
    $obtainedMarks = $item['obtained_marks'];
    $semester = $item['semester'];

    /* BLANK MARKS */

    if ($obtainedMarks === "" || $obtainedMarks === null) {

        if ($isDraft) {
            $obtainedMarks = 0;
            $status = "draft";
        } else {
            $obtainedMarks = 0;
            $status = "AB";
        }

    } else {

        if ($obtainedMarks > 30) {
            echo json_encode([
                "status" => false,
                "message" => "Marks cannot exceed 30"
            ]);
            exit;
        }

        $status = $isDraft ? "draft" : "published";
    }

    /* CHECK EXISTING MARK */

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
        SET obtained_marks=?, total_marks=?, semester=?, status=?
        WHERE student_id=? AND subject=? AND exam_type=?
        ");

        $update->bind_param(
            "iiisiss",
            $obtainedMarks,
            $totalMarks,
            $semester,
            $status,
            $studentUserId,
            $subject,
            $examType
        );

        $update->execute();

    } else {

        /* INSERT */

        $insert = $conn->prepare("
        INSERT INTO marks
        (student_id, teacher_user_id, class, subject, exam_type, total_marks, obtained_marks, semester, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $insert->bind_param(
            "iisssiiss",
            $studentUserId,
            $currentUserId,
            $class,
            $subject,
            $examType,
            $totalMarks,
            $obtainedMarks,
            $semester,
            $status
        );

        $insert->execute();
    }
}

echo json_encode([
    "status" => true,
    "message" => $isDraft ? "Draft saved successfully" : "Marks published successfully"
]);