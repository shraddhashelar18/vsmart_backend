<?php

function calculatePromotion($conn,$studentId,$atktLimit){

$stmt=$conn->prepare("
SELECT subject,
SUM(total_marks) total_marks,
SUM(obtained_marks) obtained_marks
FROM marks
WHERE student_id=?
AND exam_type='FINAL'
AND status='published'
GROUP BY subject
");

$stmt->bind_param("i",$studentId);
$stmt->execute();
$result=$stmt->get_result();

$failCount=0;
$ktSubjects=[];

$totalMarks=0;
$obtainedMarks=0;

while($row=$result->fetch_assoc()){

$total=$row['total_marks'];
$obt=$row['obtained_marks'];

$totalMarks+=$total;
$obtainedMarks+=$obt;

$percent = $total > 0 ? ($obt/$total)*100 : 0;

if($percent<40){
$failCount++;
$ktSubjects[]=$row['subject'];
}

}

if($failCount==0){
$status="PROMOTED";
}
elseif($failCount<=$atktLimit){
$status="PROMOTED_WITH_ATKT";
}
else{
$status="DETAINED";
}

if($failCount==0 && $totalMarks>0){
$percentage=round(($obtainedMarks/$totalMarks)*100,2);
}else{
$percentage=null;
}

return[
"status"=>$status,
"percentage"=>$percentage,
"backlogCount"=>$failCount,
"ktSubjects"=>$ktSubjects
];

}