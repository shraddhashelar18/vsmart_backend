<?php
require_once("../config.php");
require_once("../api_guard.php");
require_once("../cors.php");

$student_id=$_GET['user_id'];
$semester=$_GET['semester'];

$response=[];

# RESULT SUMMARY

$result=$conn->query("
SELECT percentage, marksheet_pdf
FROM semester_results
WHERE student_id='$student_id'
AND semester='$semester'
")->fetch_assoc();

if($result){
$response["percentage"]=$result["percentage"];
$response["marksheetPdf"]=$result["marksheet_pdf"];
$response["status"]=($result["percentage"]>=40)?"PASS":"FAIL";
}else{
$response["percentage"]=0;
$response["marksheetPdf"]=null;
$response["status"]="RESULT_NOT_DECLARED";
}

# ATTENDANCE %

$att=$conn->query("
SELECT status FROM attendance
WHERE student_id='$student_id'
");

$total=$att->num_rows;
$present=0;

while($r=$att->fetch_assoc()){
if($r['status']=="P")$present++;
}

if($total>0){
$attendancePercent=($present/$total)*100;
}else{
$attendancePercent=0;
}

$response["attendance"]=round($attendancePercent);

# MARKS

$marksQuery=$conn->query("
SELECT subject,exam_type,obtained_marks
FROM marks
WHERE student_id='$student_id'
AND semester='$semester'
AND status='published'
");

$subjects=[];

while($row=$marksQuery->fetch_assoc()){

$subject=$row['subject'];
$exam=$row['exam_type'];

$subjects[$subject][$exam]=$row['obtained_marks'];

}

$response["marks"]=$subjects;

echo json_encode($response);

?>