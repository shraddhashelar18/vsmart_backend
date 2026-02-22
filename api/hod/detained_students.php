<?php
require_once("../config.php");
require_once("../api_guard.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$class    = $data['class'] ?? '';
$semester = $data['semester'] ?? '';

if($class == '' || $semester == ''){
    echo json_encode([
        "status"=>false,
        "message"=>"Class and semester required"
    ]);
    exit;
}

/*
   We now use semester_results table
   Detained = percentage < 40
*/

$stmt = $conn->prepare("
    SELECT s.full_name, sr.percentage
    FROM semester_results sr
    INNER JOIN students s 
        ON s.user_id = sr.student_id
    WHERE s.class = ?
    AND sr.semester = ?
    AND sr.percentage < 40
");

$stmt->bind_param("ss",$class,$semester);
$stmt->execute();
$result = $stmt->get_result();

$data_arr = [];

while($row = $result->fetch_assoc()){
    $data_arr[] = [
        "name"=>$row['full_name'],
        "percentage"=>$row['percentage'],
        "remark"=>"Detained"
    ];
}

echo json_encode([
    "status"=>true,
    "students"=>$data_arr
]);