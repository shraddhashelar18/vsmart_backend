<?php
require_once("../../config.php");
require_once("../../api_guard.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$class = $data['class'];
$exam  = $data['exam']; // "CT1" or "CT2"

/*
We calculate:
- total obtained marks per student
- total max marks per student
*/

$stmt = $conn->prepare("
SELECT 
    s.user_id,
    s.full_name,
    SUM(m.total_marks) AS max_marks,
    SUM(m.obtained_marks) AS obtained
FROM students s
LEFT JOIN marks m 
ON s.user_id = m.student_id
AND m.class = ?
AND m.exam_type = ?
WHERE s.class = ?
GROUP BY s.user_id
");

$stmt->bind_param("sss", $class, $exam, $class);
$stmt->execute();
$result = $stmt->get_result();

$students = [];

while($row = $result->fetch_assoc()){

    $student = [];
    $student['name'] = $row['full_name'];

    if($exam == "CT1"){
        $student['ct1_total'] = $row['obtained'] ?? "ABSENT";
        $student['ct2_total'] = null;
    } else {
        $student['ct1_total'] = null;
        $student['ct2_total'] = $row['obtained'] ?? "ABSENT";
    }

    $student['max'] = $row['max_marks'] ?? 0;

    $students[] = $student;
}

echo json_encode([
    "status" => true,
    "students" => $students
]);