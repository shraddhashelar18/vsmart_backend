<?php
require_once("../config.php");
require_once("../api_guard.php");
require_once("../cors.php");


$student_id = $_GET['user_id'];

$response = [];

# ------------------------------------
# STEP 1 : GET CURRENT SEMESTER
# ------------------------------------

$semQuery = $conn->query("
SELECT current_semester
FROM students
WHERE user_id = '$student_id'
");

$semRow = $semQuery->fetch_assoc();
$current_semester = $semRow['current_semester'];

# ------------------------------------
# STEP 2 : LOOP PREVIOUS SEMESTERS
# ------------------------------------

for($sem = 1; $sem < $current_semester; $sem++){

$semesterData = [];

# ------------------------------------
# STEP 3 : FETCH SUBJECT MARKS
# ------------------------------------

$marksQuery = $conn->query("
SELECT subject, exam_type, obtained_marks, total_marks
FROM marks
WHERE student_id='$student_id'
AND semester='SEM$sem'
AND status='published'
");

$totalObtained = 0;
$totalMax = 0;

$subjects = [];

while($row = $marksQuery->fetch_assoc()){

$subject = $row['subject'];
$exam = $row['exam_type'];

$subjects[$subject][$exam] = $row['obtained_marks'];

$totalObtained += $row['obtained_marks'];
$totalMax += $row['total_marks'];

}

# ------------------------------------
# STEP 4 : CALCULATE PERCENTAGE
# ------------------------------------

$percentage = 0;

if($totalMax > 0){
$percentage = round(($totalObtained/$totalMax)*100,2);
}

# ------------------------------------
# STEP 5 : RESULT STATUS
# ------------------------------------

$status = "FAIL";

if($percentage >= 40){
$status = "PASS";
}

# ------------------------------------
# STEP 6 : CALCULATE ATTENDANCE %
# ------------------------------------

$attendanceQuery = $conn->query("
SELECT status
FROM attendance
WHERE student_id='$student_id'
");

$totalDays = $attendanceQuery->num_rows;
$presentDays = 0;

while($att = $attendanceQuery->fetch_assoc()){

if($att['status'] == "P"){
$presentDays++;
}

}

$attendancePercent = 0;

if($totalDays > 0){
$attendancePercent = round(($presentDays/$totalDays)*100,2);
}

# ------------------------------------
# STEP 7 : MONTHLY ATTENDANCE TREND
# ------------------------------------

$trendQuery = $conn->query("
SELECT MONTH(date) as month,
SUM(CASE WHEN status='P' THEN 1 ELSE 0 END) as present,
COUNT(*) as total
FROM attendance
WHERE student_id='$student_id'
GROUP BY MONTH(date)
");

$trend = [];

while($t = $trendQuery->fetch_assoc()){

$month = $t['month'];

$trend[$month] = round(($t['present']/$t['total'])*100,2);

}

# ------------------------------------
# BUILD SEMESTER DATA
# ------------------------------------

$semesterData['percentage'] = $percentage;
$semesterData['attendance'] = $attendancePercent;
$semesterData['status'] = $status;
$semesterData['attendanceTrend'] = $trend;
$semesterData['marks'] = $subjects;

$response["Semester ".$sem] = $semesterData;

}

# ------------------------------------
# RETURN JSON
# ------------------------------------

echo json_encode([
"status" => "success",
"data" => $response
]);

?>