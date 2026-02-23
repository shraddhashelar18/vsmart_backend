<?php
require_once("../config.php");
header("Content-Type: application/json");

// Read JSON body
$data = json_decode(file_get_contents("php://input"), true);

$class = $data['class'] ?? '';

if($class == ''){
    echo json_encode([
        "status"=>false,
        "message"=>"Class is required"
    ]);
    exit;
}

$stmt = $conn->prepare("
    SELECT full_name 
    FROM students 
    WHERE class = ?
");

$stmt->bind_param("s",$class);
$stmt->execute();
$result = $stmt->get_result();

$students = [];

while($row = $result->fetch_assoc()){
    $students[] = $row['full_name'];
}

echo json_encode([
    "status"=>true,
    "students"=>$students
]);