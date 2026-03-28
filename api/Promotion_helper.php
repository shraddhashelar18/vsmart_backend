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
$semData = $semQuery->get_result();

if (!$semData || $semData->num_rows == 0) {
    return [
        "status" => "DETAINED",
        "percentage" => null,
        "backlogCount" => 0,
        "ktSubjects" => []
    ];
}

$semResult = $semData->fetch_assoc();

    $currentSemester = (string) ($semResult['current_semester'] - 1);

/* calculate promotion only for that semester */

$stmt=$conn->prepare("
SELECT subject,
SUM(total_marks) total_marks,
SUM(obtained_marks) obtained_marks
FROM marks
WHERE student_id=?
AND semester = ?
AND exam_type='FINAL'
AND status='published'
GROUP BY subject
");

$stmt->bind_param("ii",$studentId,$currentSemester);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    return [
        "status" => "PENDING",
        "percentage" => null,
        "backlogCount" => 0,
        "ktSubjects" => []
    ];
}

if (!$result) {
    return [
        "status" => "DETAINED",
        "percentage" => null,
        "backlogCount" => 0,
        "ktSubjects" => []
    ];
}

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
/* ===== GET PERCENTAGE FROM semester_results ===== */

$resultStmt = $conn->prepare("
    SELECT percentage
    FROM semester_results
    WHERE student_id = ?
    AND semester = ?
");

$resultStmt->bind_param("is", $studentId, $currentSemester);
$resultStmt->execute();
$resData = $resultStmt->get_result();

if ($resData && $resData->num_rows > 0) {
    $row = $resData->fetch_assoc();
    $percentage = $row['percentage']; // ✅ correct %
} else {
    $percentage = null;
}

return[
"status"=>$status,
"percentage"=>$percentage,
"backlogCount"=>$failCount,
"ktSubjects"=>$ktSubjects
];

}