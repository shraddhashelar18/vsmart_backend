<?php
require_once("../../config.php");
require_once("../../api_guard.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$teacherId = $data['teacherId'];

/* ===============================
   GET BASIC INFO
================================ */

$stmt = $conn->prepare("
SELECT id, full_name, email, mobile_no, department,
       is_class_teacher, class_teacher_of
FROM teachers
WHERE id = ?
");

$stmt->bind_param("i", $teacherId);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    echo json_encode([
        "status" => false,
        "message" => "Teacher not found"
    ]);
    exit;
}

$teacher = $result->fetch_assoc();

/* ===============================
   GET ASSIGNMENTS
================================ */

$assignStmt = $conn->prepare("
SELECT class_name, subject
FROM teacher_assignments
WHERE teacher_id = ?
");

$assignStmt->bind_param("i", $teacherId);
$assignStmt->execute();
$assignResult = $assignStmt->get_result();

$assignments = [];

while($a = $assignResult->fetch_assoc()){
    $assignments[] = [
        "className" => $a['class_name'],
        "subject" => $a['subject']
    ];
}

/* ===============================
   RETURN RESPONSE
================================ */

echo json_encode([
    "status" => true,
    "teacher" => [
        "id" => $teacher['id'],
        "name" => $teacher['full_name'],
        "email" => $teacher['email'],
        "mobile" => $teacher['mobile_no'],
        "department" => $teacher['department'],
        "isClassTeacher" => $teacher['is_class_teacher'] == 1 ? true : false,
        "classTeacherOf" => $teacher['class_teacher_of'],
        "assignments" => $assignments
    ]
]);