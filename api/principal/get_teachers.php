<?php
require_once "../config.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$department = $data['department'];

$stmt = $conn->prepare("
    SELECT t.full_name, t.mobile_no 
    FROM teachers t
    JOIN teacher_assignments ta ON t.user_id = ta.user_id
    WHERE ta.department_code = ?
");
$stmt->bind_param("s", $department);
$stmt->execute();

$result = $stmt->get_result();

$teachers = [];

while($row = $result->fetch_assoc()){
    $teachers[] = $row;
}

echo json_encode([
    "status" => true,
    "teachers" => $teachers
]);
?>