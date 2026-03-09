<?php

function calculatePromotion($conn,$studentId,$atktLimit){

/* get student's current semester */

$semQuery = $conn->prepare("
SELECT current_semester
FROM students
WHERE user_id=?
");

$semQuery->bind_param("i",$studentId);
$semQuery->execute();
$semResult = $semQuery->get_result()->fetch_assoc();

$currentSemester = "SEM".$semResult['current_semester'];

/* calculate promotion only for that semester */

$stmt=$conn->prepare("
SELECT subject,
SUM(total_marks) total_marks,
SUM(obtained_marks) obtained_marks
FROM marks
WHERE student_id=?
AND semester LIKE CONCAT('%',?,'%')
AND exam_type='FINAL'
AND status='published'
GROUP BY subject
");

$stmt->bind_param("is",$studentId,$currentSemester);
$stmt->execute();
$result=$stmt->get_result();

$failCount=0;
$ktSubjects=[];

$totalMarks=0;
$obtainedMarks=0;

while($row=$result->fetch_assoc()){

$total=$row['total_marks'];
$obt=$row['obtained_marks'];

$totalMarks += $total;
$obtainedMarks += $obt;

$percent = $total > 0 ? ($obt/$total)*100 : 0;

if($percent < 40){
$failCount++;
$ktSubjects[] = $row['subject'];
}

}

/* promotion logic */

if($failCount == 0){
$status="PROMOTED";
}
elseif($failCount <= $atktLimit){
$status="PROMOTED_WITH_ATKT";
}
else{
$status="DETAINED";
}

/* percentage only when no backlog */

if($failCount==0 && $totalMarks>0){
$percentage = round(($obtainedMarks/$totalMarks)*100,2);
}else{
$percentage = null;
}

return[
"status"=>$status,
"percentage"=>$percentage,
"backlogCount"=>$failCount,
"ktSubjects"=>$ktSubjects
];

}