<?php
//get_students.php
require_once("../config.php");
require_once("../api_guard.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$class = $data['class'];

$stmt = $conn->prepare("
    SELECT user_id, full_name, roll_no 
    FROM students 
    WHERE class = ?
");

$stmt->bind_param("s", $class);
$stmt->execute();
$result = $stmt->get_result();

$students = [];

while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

echo json_encode([
    "status" => true,
    "students" => $students
]);
