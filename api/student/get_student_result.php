<?php
header("Content-Type: application/json");
require_once("../config.php");

/* ==============================
   READ JSON BODY (POST)
============================== */

$data = json_decode(file_get_contents("php://input"), true);

if(!isset($data['student_id'])){
    echo json_encode(["status"=>false,"message"=>"student_id required"]);
    exit;
}

$student_id = $data['student_id'];

/* =====================================================
   1️⃣ GET STUDENT DETAILS
===================================================== */

$studentQuery = $conn->prepare("
SELECT class, current_semester 
FROM students 
WHERE user_id=?
");
$studentQuery->bind_param("i",$student_id);
$studentQuery->execute();
$student = $studentQuery->get_result()->fetch_assoc();

if(!$student){
    echo json_encode(["status"=>false,"message"=>"Student not found"]);
    exit;
}

$current_class = $student['class'];
$current_sem   = $student['current_semester']; // VARCHAR

/* =====================================================
   2️⃣ DETERMINE ODD / EVEN
===================================================== */

$semester_number = (int)$current_sem;

$semester_type = ($semester_number % 2 == 0) ? "EVEN" : "ODD";

/* Active semester = current semester only */
$active_semester = $current_sem;

/* =====================================================
   3️⃣ GET SETTINGS SAFELY
===================================================== */

$settings = $conn->query("SELECT * FROM settings LIMIT 1")->fetch_assoc();

if(!$settings){
    echo json_encode(["status"=>false,"message"=>"Settings not found"]);
    exit;
}

/* =====================================================
   4️⃣ SUBJECT COUNT
===================================================== */

$subjectQuery = $conn->prepare("
SELECT COUNT(id) as total_subjects
FROM semester_subjects
WHERE semester=? AND class=?
");
$subjectQuery->bind_param("ss",$active_semester,$current_class);
$subjectQuery->execute();
$subjectCountRow = $subjectQuery->get_result()->fetch_assoc();

<<<<<<< HEAD
$subjectCount = $subjectCountRow ? $subjectCountRow['total_subjects'] : 0;
=======
if ($settingsRes->num_rows == 0) {
    echo json_encode([
        "status" => false,
        "message" => "Settings not configured"
    ]);
    exit;
}

$settings = $settingsRes->fetch_assoc();
>>>>>>> b5f3620ebd6a52d6e779168b7459e9dd09ccc8ce

$total_ct_marks = $subjectCount * 30;

/* =====================================================
   5️⃣ FETCH MARKS (VARCHAR SAFE)
===================================================== */

$marksQuery = $conn->prepare("
SELECT subject, exam_type, obtained_marks
FROM marks
WHERE student_id=? AND semester=?
");
$marksQuery->bind_param("is",$student_id,$active_semester);
$marksQuery->execute();
$marksResult = $marksQuery->get_result();

$marksData = [];
$ct1_total = 0;
$ct2_total = 0;
$final_total = 0;

while($row = $marksResult->fetch_assoc()){

    $marksData[$row['subject']][$row['exam_type']] = $row['obtained_marks'];

    if($row['exam_type']=="CT1") $ct1_total += (int)$row['obtained_marks'];
    if($row['exam_type']=="CT2") $ct2_total += (int)$row['obtained_marks'];
    if($row['exam_type']=="FINAL") $final_total += (int)$row['obtained_marks'];
}

/* =====================================================
   6️⃣ PERCENTAGE CALCULATION
===================================================== */

$ct1_percent = $total_ct_marks>0 ? round(($ct1_total/$total_ct_marks)*100,2) : 0;
$ct2_percent = $total_ct_marks>0 ? round(($ct2_total/$total_ct_marks)*100,2) : 0;

$current_sem_graph = [];

<<<<<<< HEAD
if($settings['ct1_published']=="1")
    $current_sem_graph[] = ["exam"=>"CT1","percentage"=>$ct1_percent];

if($settings['ct2_published']=="1")
    $current_sem_graph[] = ["exam"=>"CT2","percentage"=>$ct2_percent];

if($settings['final_published']=="1"){
    $final_total_marks = $subjectCount * 100;
    $final_percent = $final_total_marks>0 ? round(($final_total/$final_total_marks)*100,2) : 0;
    $current_sem_graph[] = ["exam"=>"FINAL","percentage"=>$final_percent];
=======
if ($ct1Res && isset($ct1Res['totalMax']) && $ct1Res['totalMax'] > 0) {
if ($ct1Res['totalMax'] > 0) {
    $currentSemData[0] = round(
        ($ct1Res['totalObtained'] / $ct1Res['totalMax']) * 100,
        2
    );
>>>>>>> b5f3620ebd6a52d6e779168b7459e9dd09ccc8ce
}
}

/* =====================================================
   7️⃣ ALL SEMESTER GRAPH
===================================================== */

<<<<<<< HEAD
$allSemQuery = $conn->prepare("
SELECT semester, percentage
FROM semester_results
WHERE student_id=?
ORDER BY CAST(semester AS UNSIGNED) ASC
");
$allSemQuery->bind_param("i",$student_id);
$allSemQuery->execute();
$allSemResult = $allSemQuery->get_result();

$allSemesterGraph = [];
while($row = $allSemResult->fetch_assoc()){
    $allSemesterGraph[] = $row;
=======
if ($ct2Res && isset($ct2Res['totalMax']) && $ct2Res['totalMax'] > 0) {
if ($ct2Res['totalMax'] > 0) {
    $currentSemData[1] = round(
        ($ct2Res['totalObtained'] / $ct2Res['totalMax']) * 100,
        2
    );
>>>>>>> b5f3620ebd6a52d6e779168b7459e9dd09ccc8ce
}
}

<<<<<<< HEAD
/* =====================================================
   FINAL RESPONSE
===================================================== */
=======
// ---- Final Percentage (From semester_results table) ----
$finalPercentStmt = $conn->prepare("
    SELECT percentage 
    FROM semester_results
    WHERE student_id = ?
    AND semester = ?
");
$finalPercentStmt->bind_param("is", $userId, $semesterCode);
$finalPercentStmt->execute();
$finalPercentRes = $finalPercentStmt->get_result();

if ($finalPercentRes && $finalPercentRes->num_rows > 0) {
if ($finalPercentRes->num_rows > 0) {
    $finalRow = $finalPercentRes->fetch_assoc();
    $currentSemData[2] = (float)$finalRow['percentage'];
}
}

/* =========================================
   7️⃣ All Semester Graph Data
========================================= */

$allSemData = [];

$allStmt = $conn->prepare("
    SELECT semester, percentage
    FROM semester_results
    WHERE student_id = ?
    ORDER BY semester ASC
");
$allStmt->bind_param("i", $userId);
$allStmt->execute();
$allRes = $allStmt->get_result();

while ($s = $allRes->fetch_assoc()) {
    $allSemData[] = (float)$s['percentage'];
}

/* =========================================
   8️⃣ Final Response (Matches Flutter Model)
========================================= */
>>>>>>> b5f3620ebd6a52d6e779168b7459e9dd09ccc8ce

echo json_encode([
    "status"=>true,
    "semester_type"=>$semester_type, // ODD or EVEN
    "current_class"=>$current_class,
    "current_semester"=>$current_sem,
    "active_semester"=>$active_semester,
    "ct1_published"=>$settings['ct1_published'],
    "ct2_published"=>$settings['ct2_published'],
    "final_published"=>$settings['final_published'],
    "total_ct_marks"=>$total_ct_marks,
    "ct1_total"=>$ct1_total,
    "ct1_percentage"=>$ct1_percent,
    "ct2_total"=>$ct2_total,
    "ct2_percentage"=>$ct2_percent,
    "marks"=>$marksData,
    "current_sem_performance_graph"=>$current_sem_graph,
    "all_semester_graph"=>$allSemesterGraph,
    "allow_marksheet_upload"=>$settings['allow_marksheet_upload'],
    "allow_reupload"=>$settings['allow_reupload']
]);