<?php
require_once("../../config.php");
require_once("../../api_guard.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$department = $data['department'];

$stmt = $conn->prepare("
SELECT id, full_name, email, mobile_no, department,
       is_class_teacher, class_teacher_of
FROM teachers
WHERE department = ?
");

$stmt->bind_param("s", $department);
$stmt->execute();
$result = $stmt->get_result();

$teachers = [];

while($row = $result->fetch_assoc()){

    $teachers[] = [
        "id" => $row['id'],
        "name" => $row['full_name'],
        "email" => $row['email'],
        "mobile" => $row['mobile_no'],
        "department" => $row['department'],
        "isClassTeacher" => $row['is_class_teacher'] == 1 ? true : false,
        "classTeacherOf" => $row['class_teacher_of']
    ];
}

echo json_encode([
    "status" => true,
    "teachers" => $teachers
]);
