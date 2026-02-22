<?php
require_once("../config.php");
require_once("../api_guard.php");

header("Content-Type: application/json");

// Read JSON body
$data = json_decode(file_get_contents("php://input"), true);

$department = $data['department'] ?? '';

if($department == ''){
    echo json_encode([
        "status"=>false,
        "message"=>"Department required"
    ]);
    exit;
}

/* Department → Class Prefix Logic */
$prefix = ($department == 'IT') ? 'IF' : $department;

/* Get Distinct Classes */
$stmt = $conn->prepare("
    SELECT DISTINCT class 
    FROM students 
    WHERE class LIKE CONCAT(?, '%')
");

$stmt->bind_param("s",$prefix);
$stmt->execute();
$result = $stmt->get_result();

$classes = [];

while($row = $result->fetch_assoc()){
    $classes[] = $row['class'];
}

echo json_encode([
    "status"=>true,
    "department"=>$department,
    "classes"=>$classes
]);