<?php

require_once("../../config.php");
require_once("../../api_guard.php");
require_once("../../cors.php");

header("Content-Type: application/json");

/* =========================
   READ INPUT
========================= */

$data = json_decode(file_get_contents("php://input"), true);

$className = $data['class_name'] ?? null;

if(!$className){
    echo json_encode([
        "status"=>false,
        "message"=>"class_name required"
    ]);
    exit;
}

/* =========================
   GET CLASS INFO
========================= */

$stmt = $conn->prepare("
SELECT department, semester 
FROM classes 
WHERE class_name = ?
");

$stmt->bind_param("s",$className);
$stmt->execute();
$res = $stmt->get_result();

if($res->num_rows == 0){
    echo json_encode([
        "status"=>false,
        "message"=>"Class not found"
    ]);
    exit;
}

$class = $res->fetch_assoc();

$department = $class['department'];
$semester = $class['semester'];

/* =========================
   FETCH SUBJECTS
========================= */

$stmt = $conn->prepare("
SELECT subject_name 
FROM semester_subjects
WHERE department = ? AND semester = ?
ORDER BY subject_name
");

$stmt->bind_param("si",$department,$semester);
$stmt->execute();

$res = $stmt->get_result();

$subjects = [];

while($row = $res->fetch_assoc()){
    $subjects[] = $row['subject_name'];
}

/* =========================
   RESPONSE
========================= */

echo json_encode([
    "status"=>true,
    "subjects"=>$subjects
]);