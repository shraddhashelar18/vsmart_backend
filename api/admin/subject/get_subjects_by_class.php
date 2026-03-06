<?php
//add teacher.php
require_once("../../config.php");
require_once("../../api_guard.php");
require_once("../../cors.php");

header("Content-Type: application/json");

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
   DERIVE CLASS GROUP
========================= */

$department = substr($className,0,2);
$semester = substr($className,2,1);

$classGroup = $department.$semester."K";

/* =========================
   FETCH SUBJECTS
========================= */

$stmt = $conn->prepare("
SELECT subject_name
FROM semester_subjects
WHERE class = ? AND semester = ?
ORDER BY subject_name
");

$stmt->bind_param("si",$classGroup,$semester);
$stmt->execute();

$res = $stmt->get_result();

$subjects = [];

while($row = $res->fetch_assoc()){
    $subjects[] = $row['subject_name'];
}

echo json_encode([
    "status"=>true,
    "subjects"=>$subjects
]);