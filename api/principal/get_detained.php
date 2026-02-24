<?php
require_once "../config.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$department_code = $data['department'];

$stmt = $conn->prepare("
    SELECT full_name, roll_no 
    FROM students 
    WHERE department_code=? AND status='detained'
");
$stmt->bind_param("s", $department_code);
$stmt->execute();

$result = $stmt->get_result();

$data = [];

while($row = $result->fetch_assoc()){
    $data[] = $row;
}

echo json_encode([
    "status" => true,
    "detained_students" => $data
]);
?>