<?php
require_once("../config.php");
require_once("../api_guard.php");
require_once("../cors.php");

$student_id = $_GET['user_id'] ?? null;

if(!$student_id){
 echo json_encode(["status"=>"error","message"=>"Student ID required"]);
 exit;
}

$response = [];

# ------------------------------------
# STEP 1 : GET CURRENT SEMESTER
# ------------------------------------

$semQuery = $conn->query("
SELECT current_semester, roll_no
FROM students
WHERE user_id='$student_id'
");

$semRow = $semQuery->fetch_assoc();

$current_semester = $semRow['current_semester'];
$rollNo = $semRow['roll_no'];

# ------------------------------------
# STEP 2 : LOOP PREVIOUS SEMESTERS
# ------------------------------------

for($sem=1;$sem<$current_semester;$sem++){

$semesterData = new stdClass();

# ------------------------------------
# STEP 3 : FETCH SUBJECT MARKS
# ------------------------------------

$marksQuery = $conn->query("
SELECT subject,exam_type,obtained_marks,total_marks
FROM marks
WHERE student_id='$student_id'
AND semester='$sem'
AND status='published'
");

$totalObtained=0;
$totalMax=0;

$subjects=[];

while($row=$marksQuery->fetch_assoc()){

$subject=$row['subject'];
$exam=$row['exam_type'];

if(!isset($subjects[$subject])){
 $subjects[$subject]=[];
}

$subjects[$subject][$exam]=$row['obtained_marks'];

$totalObtained+=$row['obtained_marks'];
$totalMax+=$row['total_marks'];

}

# ------------------------------------
# STEP 4 : CALCULATE PERCENTAGE
# ------------------------------------

$percentage=0;

if($totalMax>0){
$percentage=round(($totalObtained/$totalMax)*100,2);
}

# ------------------------------------
# STEP 5 : RESULT STATUS
# ------------------------------------

$status="FAIL";

if($percentage>=40){
$status="PASS";
}

# ------------------------------------
# STEP 6 : ATTENDANCE %
# ------------------------------------

$attendanceQuery = $conn->query("
SELECT status
FROM attendance
WHERE student_id='$student_id'
AND semester='$sem'
");

$totalDays=$attendanceQuery->num_rows;
$presentDays=0;

while($att=$attendanceQuery->fetch_assoc()){

if($att['status']=="P"){
$presentDays++;
}

}

$attendancePercent=0;

if($totalDays>0){
$attendancePercent=round(($presentDays/$totalDays)*100,2);
}

# ------------------------------------
# STEP 7 : MONTHLY ATTENDANCE TREND
# ------------------------------------

$trendQuery = $conn->query("
SELECT MONTHNAME(date) as month,
SUM(CASE WHEN status='P' THEN 1 ELSE 0 END) as present,
COUNT(*) as total
FROM attendance
WHERE student_id='$student_id'
AND semester='$sem'
GROUP BY MONTH(date)
ORDER BY MONTH(date)
");

$trend=new stdClass();

while($t=$trendQuery->fetch_assoc()){

$month=$t['month'];

$trend->$month=round(($t['present']/$t['total'])*100,2);

}

# ------------------------------------
# BUILD SEMESTER DATA
# ------------------------------------

$marksheetQuery = $conn->query("
SELECT marksheet_pdf
FROM semester_results
WHERE student_id='$student_id'
AND semester='$sem'
");

$marksheetPdf = null;

if($marksheetQuery && $marksheetQuery->num_rows > 0){
    $marksheetRow = $marksheetQuery->fetch_assoc();
    $marksheetPdf = $marksheetRow['marksheet_pdf'];
}

$semesterData->percentage=$percentage;
$semesterData->attendance=$attendancePercent;
$semesterData->status=$status;
$semesterData->attendanceTrend=$trend;
$semesterData->marks=$subjects;
$semesterData->marksheetPdf = $marksheetPdf
    ? BASE_URL.$marksheetPdf
    : null;

$response["Semester ".$sem]=$semesterData;

}

# ------------------------------------
# RETURN JSON
# ------------------------------------

echo json_encode([
"status"=>"success",
"data"=>$response
]);
?>