<?php
//get_grades.php
header("Content-Type: application/json");
require_once "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['enrollment'])) {
    echo json_encode(["status" => false, "message" => "enrollment required"]);
    exit;
}

$enrollment = $data['enrollment'];
$semester = $data['semester'] ?? 1;

# Get student user_id
$studentQuery = $conn->prepare("
SELECT user_id, current_semester 
FROM students 
WHERE enrollment_no=?
");
$studentQuery->bind_param("s", $enrollment);
$studentQuery->execute();
$result = $studentQuery->get_result();

if ($result->num_rows == 0) {
    echo json_encode(["status" => false, "message" => "Student not found"]);
    exit;
}

$student = $result->fetch_assoc();

$student_id = $student['user_id'];
$semester = $student['current_semester'];

$ct1Marks = [];
$ct2Marks = [];

$marksQuery = $conn->prepare("
SELECT subject, exam_type, obtained_marks
FROM marks
WHERE student_id=? 
AND semester=? 
AND exam_type IN ('CT1','CT2')
");

$marksQuery->bind_param("is", $student_id, $semester);
$marksQuery->execute();
$marksResult = $marksQuery->get_result();

while ($row = $marksResult->fetch_assoc()) {

    if ($row['exam_type'] == "CT1") {
        $ct1Marks[$row['subject']] = (int)($row['obtained_marks'] ?? 0);
    }

    if ($row['exam_type'] == "CT2") {
        $ct2Marks[$row['subject']] = (int)($row['obtained_marks'] ?? 0);
    }
}

echo json_encode([
    "status" => true,
    "ct1Marks" => (object)$ct1Marks,
    "ct2Marks" => (object)$ct2Marks
]);
?>