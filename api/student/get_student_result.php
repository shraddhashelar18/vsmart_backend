<?php
require_once("../config.php");
require_once("../api_guard.php");

header("Content-Type: application/json");

/* =========================================
   1️⃣ Allow Only Student
========================================= */

if ($currentRole != 'student') {
    echo json_encode([
        "status" => false,
        "message" => "Access Denied"
    ]);
    exit;
}

$userId = $currentUserId;

/* =========================================
   2️⃣ Get Student Semester (SEM6 format)
========================================= */

$stmt = $conn->prepare("
    SELECT current_semester 
    FROM students 
    WHERE user_id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    echo json_encode(["status" => false]);
    exit;
}

$row = $res->fetch_assoc();
$semesterCode = $row['current_semester'];   // e.g. SEM6

// Extract number for frontend (6)
$semesterNumber = (int) filter_var($semesterCode, FILTER_SANITIZE_NUMBER_INT);

/* =========================================
   3️⃣ Check CT1 / CT2 Declared
========================================= */

$ct1Declared = false;
$ct2Declared = false;

$checkStmt = $conn->prepare("
    SELECT exam_type 
    FROM marks 
    WHERE student_id = ? 
    AND semester = ?
");
$checkStmt->bind_param("is", $userId, $semesterCode);
$checkStmt->execute();
$checkRes = $checkStmt->get_result();

while ($m = $checkRes->fetch_assoc()) {

    if ($m['exam_type'] == 'CT-1') {
        $ct1Declared = true;
    }

    if ($m['exam_type'] == 'CT-2') {
        $ct2Declared = true;
    }
}

/* =========================================
   4️⃣ Final Upload & Publish Settings
========================================= */

$settingsRes = $conn->query("
    SELECT allow_marksheet_upload, allow_reupload
    FROM settings
    LIMIT 1
");

if ($settingsRes->num_rows == 0) {
    echo json_encode([
        "status" => false,
        "message" => "Settings not configured"
    ]);
    exit;
}

$settings = $settingsRes->fetch_assoc();

$finalUploadAllowed = (bool)$settings['allow_marksheet_upload'];
$reuploadAllowed = (bool)$settings['allow_reupload'];

/* =========================================
   5️⃣ Check Final Marksheet Uploaded
========================================= */

$finalPdfUploaded = false;
$finalDeclared = false;

$finalStmt = $conn->prepare("
    SELECT id 
    FROM final_marksheets
    WHERE user_id = ?
    AND semester = ?
");
$finalStmt->bind_param("is", $userId, $semesterCode);
$finalStmt->execute();
$finalRes = $finalStmt->get_result();

if ($finalRes->num_rows > 0) {
    $finalPdfUploaded = true;
    $finalDeclared = true;
}

/* =========================================
   6️⃣ Current Semester CT Percentages
   (CT1, CT2, Final placeholder)
========================================= */

$currentSemData = [0, 0, 0];  // CT1, CT2, Final

// ---- CT1 ----
$ct1Stmt = $conn->prepare("
    SELECT SUM(obtained_marks) as totalObtained,
           SUM(total_marks) as totalMax
    FROM marks
    WHERE student_id = ?
    AND semester = ?
    AND exam_type = 'CT-1'
");
$ct1Stmt->bind_param("is", $userId, $semesterCode);
$ct1Stmt->execute();
$ct1Res = $ct1Stmt->get_result()->fetch_assoc();

if ($ct1Res && isset($ct1Res['totalMax']) && $ct1Res['totalMax'] > 0) {
if ($ct1Res['totalMax'] > 0) {
    $currentSemData[0] = round(
        ($ct1Res['totalObtained'] / $ct1Res['totalMax']) * 100,
        2
    );
}
}

// ---- CT2 ----
$ct2Stmt = $conn->prepare("
    SELECT SUM(obtained_marks) as totalObtained,
           SUM(total_marks) as totalMax
    FROM marks
    WHERE student_id = ?
    AND semester = ?
    AND exam_type = 'CT-2'
");
$ct2Stmt->bind_param("is", $userId, $semesterCode);
$ct2Stmt->execute();
$ct2Res = $ct2Stmt->get_result()->fetch_assoc();

if ($ct2Res && isset($ct2Res['totalMax']) && $ct2Res['totalMax'] > 0) {
if ($ct2Res['totalMax'] > 0) {
    $currentSemData[1] = round(
        ($ct2Res['totalObtained'] / $ct2Res['totalMax']) * 100,
        2
    );
}
}

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

echo json_encode([
    "status" => true,
    "semester" => $semesterNumber,
    "ct1Declared" => $ct1Declared,
    "ct2Declared" => $ct2Declared,
    "finalDeclared" => $finalDeclared,
    "finalUploadAllowed" => $finalUploadAllowed,
    "finalPdfUploaded" => $finalPdfUploaded,
    "reuploadAllowed" => $reuploadAllowed,
    "currentSemData" => $currentSemData,
    "allSemData" => $allSemData
]);