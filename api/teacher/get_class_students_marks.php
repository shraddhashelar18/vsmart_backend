<?php
require_once("../config.php");
require_once("../api_guard.php");

header("Content-Type: application/json");

$class = $_GET['class'] ?? '';
$subject = $_GET['subject'] ?? '';
$examType = $_GET['exam_type'] ?? '';

if (empty($class) || empty($subject) || empty($examType)) {
    echo json_encode([
        "status" => false,
        "message" => "Class, Subject and Exam Type required"
    ]);
    exit;
}

/* FETCH STUDENTS */

$stmt = $conn->prepare("
SELECT user_id, full_name, roll_no,current_semester
FROM students
WHERE class = ?
ORDER BY roll_no ASC
");

$stmt->bind_param("s", $class);
$stmt->execute();
$result = $stmt->get_result();

$students = [];

while ($row = $result->fetch_assoc()) {

    $studentId = $row['user_id'];

    /* CHECK MARKS */

    $marksStmt = $conn->prepare("
    SELECT obtained_marks, status
    FROM marks
    WHERE student_id=? AND subject=? AND exam_type=?
    ");

    $marksStmt->bind_param("iss", $studentId, $subject, $examType);
    $marksStmt->execute();
    $marksRes = $marksStmt->get_result();

    $marks = 0;
    $status = "draft";

    if ($marksRes->num_rows > 0) {
        $data = $marksRes->fetch_assoc();
        $marks = $data['obtained_marks'];
        $status = $data['status'];
    }

    $students[] = [
        "user_id" => $studentId,
        "name" => $row['full_name'],
        "roll" => $row['roll_no'],
        "current_semester" => $row['current_semester'],
        "marks" => $marks,
        "status" => $status
    ];
}

echo json_encode([
    "status" => true,
    "students" => $students
]);