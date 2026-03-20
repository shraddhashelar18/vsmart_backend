<?php
//get_marks_stats.php
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
$class = $_GET['class'] ?? '';
$subject = trim($_GET['subject'] ?? '');
$examType = $_GET['exam_type'] ?? '';

if (empty($class) || empty($subject) || empty($examType)) {
    echo json_encode([
        "status" => false,
        "message" => "Class, Subject and Exam Type required"
    ]);
    exit;
}

/* TOTAL STUDENTS */

$totalStmt = $conn->prepare("
SELECT COUNT(*) as total
FROM students
WHERE class=?
");

$totalStmt->bind_param("s", $class);
$totalStmt->execute();
$totalRes = $totalStmt->get_result()->fetch_assoc();

$totalStudents = $totalRes['total'];

/* MARKS DATA */

$marksStmt = $conn->prepare("
SELECT obtained_marks, status
FROM marks
WHERE class=? AND subject=? AND exam_type=?
");

$marksStmt->bind_param("sss", $class, $subject, $examType);
$marksStmt->execute();
$marksRes = $marksStmt->get_result();

$completed = 0;
$sum = 0;

while ($row = $marksRes->fetch_assoc()) {

    if ($row['status'] != 'AB') {
        $completed++;
        $sum += $row['obtained_marks'];
    }
}

$average = $completed > 0 ? $sum / $completed : 0;

echo json_encode([
    "status" => true,
    "max_marks" => 30,
    "completed" => $completed,
    "total_students" => $totalStudents,
    "average" => round($average, 1)
]);