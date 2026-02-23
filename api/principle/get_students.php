<?php
require_once "../config.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$department = $data['department'];
$class = $data['class'];

$stmt = $conn->prepare("SELECT full_name, roll_no 
                        FROM students 
                        WHERE department=? AND class=?");
$stmt->bind_param("ss", $department, $class);
$stmt->execute();

$result = $stmt->get_result();

$students = [];

while($row = $result->fetch_assoc()){
    $students[] = $row;
}

echo json_encode([
    "status" => true,
    "students" => $students
]);
?>
