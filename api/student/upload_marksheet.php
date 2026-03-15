<?php

require_once("../config.php");
require_once("../api_guard.php");
require_once("../cors.php");
require_once("../../vendor/autoload.php");

use Smalot\PdfParser\Parser;

header("Content-Type: application/json");

/* ================= ROLE CHECK ================= */

if($currentRole!="student"){
echo json_encode([
"status"=>false,
"message"=>"Access denied"
]);
exit;
}

$student_id=$currentUserId;

/* ================= FILE CHECK ================= */

if(!isset($_FILES['marksheet'])){
echo json_encode([
"status"=>false,
"message"=>"Marksheet required"
]);
exit;
}

$file=$_FILES['marksheet'];

/* ================= FILE ERROR CHECK ================= */

if($file['error'] !== 0){
echo json_encode([
"status"=>false,
"message"=>"File upload failed"
]);
exit;
}

/* ================= FILE VALIDATION ================= */

$ext=strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));

if($ext!="pdf"){
echo json_encode([
"status"=>false,
"message"=>"Only PDF allowed"
]);
exit;
}

if($file['size']>5*1024*1024){
echo json_encode([
"status"=>false,
"message"=>"File too large"
]);
exit;
}

/* ================= SAVE FILE ================= */

$uploadDir="../uploads/marksheets/";

if(!file_exists($uploadDir)){
mkdir($uploadDir,0777,true);
}

$fileName=$student_id."_".time().".pdf";

$target=$uploadDir.$fileName;

if(!move_uploaded_file($file['tmp_name'],$target)){
echo json_encode([
"status"=>false,
"message"=>"Failed to save file"
]);
exit;
}

/* ================= READ PDF ================= */

try{

$parser=new Parser();
$pdf=$parser->parseFile($target);
$text=$pdf->getText();

}catch(Exception $e){

echo json_encode([
"status"=>false,
"message"=>"Unable to read marksheet"
]);
exit;

}

if(empty($text)){
echo json_encode([
"status"=>false,
"message"=>"Marksheet text not readable"
]);
exit;
}

/* ================= DETECT SEMESTER ================= */

preg_match('/(FIRST|SECOND|THIRD|FOURTH|FIFTH|SIXTH)\s+SEMESTER/i',$text,$semMatch);

$semesterMap=[
"FIRST"=>1,
"SECOND"=>2,
"THIRD"=>3,
"FOURTH"=>4,
"FIFTH"=>5,
"SIXTH"=>6
];

if(!isset($semMatch[1])){
echo json_encode([
"status"=>false,
"message"=>"Semester not detected"
]);
exit;
}

$semester="SEM".$semesterMap[strtoupper($semMatch[1])];

/* ================= DUPLICATE CHECK ================= */

$check=$conn->query("
SELECT student_id FROM semester_results
WHERE student_id='$student_id'
AND semester='$semester'
");

if($check->num_rows>0){
echo json_encode([
"status"=>false,
"message"=>"Marksheet already uploaded"
]);
exit;
}

/* ================= EXTRACT PERCENTAGE ================= */

preg_match('/\b([0-9]{2}\.[0-9]{2})\b/', $text, $percentMatch);

$percentage = $percentMatch[1] ?? 0;

if($percentage == 0){
echo json_encode([
"status"=>false,
"message"=>"Percentage not detected"
]);
exit;
}

/* ================= SAVE RESULT ================= */

$stmt=$conn->prepare("
INSERT INTO semester_results
(student_id,semester,percentage,marksheet_pdf)
VALUES (?,?,?,?)
");

$stmt->bind_param(
"isis",
$student_id,
$semester,
$percentage,
$fileName
);

$stmt->execute();

/* ================= UPDATE STUDENT ================= */

$conn->query("
UPDATE students
SET marks_uploaded=1
WHERE user_id='$student_id'
");

echo json_encode([
"status"=>true,
"message"=>"Marksheet uploaded successfully",
"semester"=>$semester,
"percentage"=>$percentage
]);

?>