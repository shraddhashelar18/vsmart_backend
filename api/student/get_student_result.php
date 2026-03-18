<?php
header("Content-Type: application/json");
error_reporting(0);
ini_set('display_errors', 0);

require_once("../config.php");
require_once "../cors.php"; 
require_once "../api_guard.php"; // ✅ ADDED

/* ================= ROLE CHECK ================= */
if ($currentRole != 'student') {
    echo json_encode([
        "status" => false,
        "message" => "Access Denied"
    ]);
    exit;
}

/* ==============================
   READ JSON BODY
============================== */

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['student_id'])) {
    echo json_encode(["status" => false, "message" => "student_id required"]);
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

$studentQuery->bind_param("i", $student_id);
$studentQuery->execute();
$student = $studentQuery->get_result()->fetch_assoc();

if (!$student) {
    echo json_encode(["status" => false, "message" => "Student not found"]);
    exit;
}

$current_class = $student['class'];
$current_sem = $student['current_semester'];

$semester_number = (int) $current_sem;
$semester_type = ($semester_number % 2 == 0) ? "EVEN" : "ODD";

/* =====================================================
   2️⃣ DETERMINE ACTIVE SEMESTER
===================================================== */

$active_semester = $current_sem;

/* Check if marks exist for current semester */

$check = $conn->prepare("
SELECT COUNT(*) as cnt
FROM marks
WHERE student_id=? 
AND semester=?
");

$check->bind_param("ii", $student_id, $current_sem);
$check->execute();
$row = $check->get_result()->fetch_assoc();

/* If no marks in current semester → show previous semester */

if ($row['cnt'] == 0 && $current_sem > 1) {
    $active_semester = $current_sem - 1;
}

/* =====================================================
   3️⃣ GET SETTINGS
===================================================== */

$settings = $conn->query("SELECT * FROM settings LIMIT 1")->fetch_assoc();

/* =====================================================
   4️⃣ SUBJECT COUNT
===================================================== */

$classPrefix = $current_class;

$subjectQuery = $conn->prepare("
SELECT COUNT(id) as total_subjects
FROM semester_subjects
WHERE semester=? AND class=?
");

$subjectQuery->bind_param("is", $active_semester, $classPrefix);
$subjectQuery->execute();

$subjectRow = $subjectQuery->get_result()->fetch_assoc();
$subjectCount = $subjectRow['total_subjects'];

/* =====================================================
   5️⃣ CHECK CT1 / CT2 PUBLISH STATUS
===================================================== */

$ct1Check = $conn->prepare("
SELECT COUNT(DISTINCT subject) as cnt
FROM marks
WHERE student_id=? 
AND semester=? 
AND exam_type='CT1'
AND status='published'
");

$ct1Check->bind_param("ii", $student_id, $active_semester);
$ct1Check->execute();
$ct1Row = $ct1Check->get_result()->fetch_assoc();

if ($active_semester != $current_sem) {
    $ct1_published = "1";
} else {
    $ct1_published = ($ct1Row['cnt'] > 0) ? "1" : "0";
}

$ct2Check = $conn->prepare("
SELECT COUNT(DISTINCT subject) as cnt
FROM marks
WHERE student_id=? 
AND semester=? 
AND exam_type='CT2'
AND status='published'
");

$ct2Check->bind_param("ii", $student_id, $active_semester);
$ct2Check->execute();
$ct2Row = $ct2Check->get_result()->fetch_assoc();

if ($active_semester != $current_sem) {
    $ct2_published = "1";
} else {
    $ct2_published = ($ct2Row['cnt'] > 0) ? "1" : "0";
}

/* =====================================================
   6️⃣ FETCH MARKS
===================================================== */

$marksQuery = $conn->prepare("
SELECT subject, exam_type, obtained_marks
FROM marks
WHERE student_id=? 
AND semester=? 
AND status='published'
");

$marksQuery->bind_param("is", $student_id, $active_semester);
$marksQuery->execute();
$marksResult = $marksQuery->get_result();

$marksData = [];

$ct1_total = 0;
$ct2_total = 0;
$final_total = 0;

while ($row = $marksResult->fetch_assoc()) {

    $marksData[$row['subject']][$row['exam_type']] = $row['obtained_marks'];

    if ($row['exam_type'] == "CT1")
        $ct1_total += (int) $row['obtained_marks'];
    if ($row['exam_type'] == "CT2")
        $ct2_total += (int) $row['obtained_marks'];
    if ($row['exam_type'] == "FINAL")
        $final_total += (int) $row['obtained_marks'];
}

$total_ct_marks = count($marksData) * 30;

/* =====================================================
   7️⃣ PERCENTAGE CALCULATION
===================================================== */

$ct1_percent = $total_ct_marks > 0 ? round(($ct1_total / $total_ct_marks) * 100, 2) : 0;
$ct2_percent = $total_ct_marks > 0 ? round(($ct2_total / $total_ct_marks) * 100, 2) : 0;

$current_sem_graph = [];

if ($ct1_published == "1") {
    $current_sem_graph[] = ["exam" => "CT1", "percentage" => $ct1_percent];
}

if ($ct2_published == "1") {
    $current_sem_graph[] = ["exam" => "CT2", "percentage" => $ct2_percent];
}

$final_published = $settings['final_published'] ?? "0";

if ($final_published == "1") {

    $finalQuery = $conn->prepare("
    SELECT percentage
    FROM semester_results
    WHERE student_id=? AND semester=?
    ");

    $finalQuery->bind_param("ii", $student_id, $active_semester);
    $finalQuery->execute();

    $finalRow = $finalQuery->get_result()->fetch_assoc();

    $final_percent = $finalRow['percentage'] ?? 0;

    $current_sem_graph[] = [
        "exam" => "FINAL",
        "percentage" => (float)$final_percent
    ];
}

/* =====================================================
   8️⃣ ALL SEMESTER GRAPH
===================================================== */

$allSemQuery = $conn->prepare("
SELECT semester, percentage
FROM semester_results
WHERE student_id=?
ORDER BY CAST(REPLACE(semester,'SEM','') AS UNSIGNED)
");

$allSemQuery->bind_param("i", $student_id);
$allSemQuery->execute();

$allSemResult = $allSemQuery->get_result();

$allSemesterGraph = [];

while ($row = $allSemResult->fetch_assoc()) {

    $allSemesterGraph[] = [
        "semester" => (int) $row["semester"],
        "percentage" => (float) $row["percentage"]
    ];
}

$uploadAllowed = 1;

$checkUpload = $conn->prepare("
SELECT marks_uploaded 
FROM students 
WHERE user_id=?
");

$checkUpload->bind_param("i", $student_id);
$checkUpload->execute();
$row = $checkUpload->get_result()->fetch_assoc();

$uploadAllowed = 0;

if ($settings['allow_marksheet_upload'] == 1 && $row['marks_uploaded'] == 0) {
    $uploadAllowed = 1;
}

/* =====================================================
   FINAL RESPONSE
===================================================== */

echo json_encode([

    "status" => true,
    "semester_type" => $semester_type,
    "current_class" => $current_class,
    "current_semester" => $current_sem,
    "active_semester" => $active_semester,
    "allow_marksheet_upload" => $uploadAllowed,
    "ct1_published" => $ct1_published,
    "ct2_published" => $ct2_published,
    "final_published" => $final_published,

    "ct1_total" => $ct1_total,
    "ct2_total" => $ct2_total,

    "ct1_percentage" => $ct1_percent,
    "ct2_percentage" => $ct2_percent,

    "marks" => empty($marksData) ? new stdClass() : $marksData,

    "current_sem_performance_graph" => $current_sem_graph,
    "all_semester_graph" => $allSemesterGraph,

    "allow_marksheet_upload" => $uploadAllowed,
    "allow_reupload" => $settings['allow_reupload']
]);
?>