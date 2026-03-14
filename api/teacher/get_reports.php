<?php
//get_reports.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../config.php");
require_once("../api_guard.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $data['user_id'] ?? '';

if(!$user_id){
    echo json_encode([
        "status"=>false,
        "message"=>"User ID required"
    ]);
    exit;
}

/* ===============================
   GET CURRENT SEMESTER
================================ */

$semStmt = $conn->prepare("
SELECT current_semester 
FROM students 
WHERE user_id=?
");
if(!$semStmt){
    echo json_encode([
        "status"=>false,
        "message"=>$conn->error
    ]);
    exit;
}

$semStmt->bind_param("i",$user_id);
$semStmt->execute();
$semResult = $semStmt->get_result();

if($semResult->num_rows == 0){
    echo json_encode([
        "status"=>false,
        "message"=>"Student not found"
    ]);
    exit;
}

$semRow = $semResult->fetch_assoc();
$currentSemester = $semRow['current_semester'];


/* ===============================
   GET MARKS FOR CURRENT SEM
================================ */

$stmt = $conn->prepare("
SELECT subject, exam_type, obtained_marks, total_marks, status
FROM marks
WHERE student_id=? AND semester=? AND status!='draft'
");

if(!$stmt){
    echo json_encode([
        "status"=>false,
        "message"=>$conn->error
    ]);
    exit;
}

$stmt->bind_param("is",$user_id,$currentSemester);
$stmt->execute();
$result = $stmt->get_result();

$marks = [];

while($row=$result->fetch_assoc()){

    $subject=$row['subject'];
    $exam=$row['exam_type'];

    if(!isset($marks[$subject])){
        $marks[$subject]=[];
    }

    $marks[$subject][$exam]=[
        "score"=>$row['obtained_marks'] === null ? null : $row['obtained_marks'],
        "max"=>$row['total_marks'],
        "status"=>$row['status']
    ];
}

echo json_encode([
    "status"=>true,
    "marks"=>$marks
]);