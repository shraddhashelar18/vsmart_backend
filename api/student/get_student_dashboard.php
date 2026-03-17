<?php
require_once("../config.php");
require_once("../api_guard.php");

header("Content-Type: application/json");

error_reporting(E_ALL);
ini_set('display_errors', 0);

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
   2️⃣ Fetch Student Details
========================================= */

$stmt = $conn->prepare("
    SELECT full_name, roll_no, class, current_semester, department
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

$student = $res->fetch_assoc();

$studentName = $student['full_name'];
$rollNo = $student['roll_no'];
$className = $student['class'];
$semesterCode = $student['current_semester'];
$department = $student['department'];

$semesterNumber = (int) filter_var($semesterCode, FILTER_SANITIZE_NUMBER_INT);


/* =========================================
   4️⃣ CURRENT MONTH ATTENDANCE
========================================= */

$presentDays = 0;
$absentDays = 0;

$attStmt = $conn->prepare("
    SELECT status
    FROM attendance
    WHERE student_id = ?
    AND semester = ?
    AND MONTH(date) = MONTH(CURRENT_DATE())
    AND YEAR(date) = YEAR(CURRENT_DATE())
");
$attStmt->bind_param("ii", $userId, $semesterNumber);
$attStmt->execute();
$attRes = $attStmt->get_result();

while ($row = $attRes->fetch_assoc()) {

    $status = strtoupper($row['status']);

    if ($status == 'P' || $status == 'L') {
        $presentDays++;
    }

    if ($status == 'A') {
        $absentDays++;
    }
}

/* =========================================
   5️⃣ Detect Semester Cycle
========================================= */

$settings = $conn->query("SELECT active_semester FROM settings LIMIT 1");
$cycle = $settings->fetch_assoc()['active_semester'];
$currentMonth = date("n");
if ($cycle == "EVEN") {
    $months = [12, 1, 2, 3, 4, 5];
} else {
    $months = [6, 7, 8, 9, 10, 11];
}

$currentYear = date("Y");

/* =========================================
   6️⃣ PERFORMANCE TREND (Semester Months)
========================================= */

$performanceTrend = [];

foreach ($months as $month) {

    // Skip future months ONLY for current semester
    if ($semesterNumber == $student['current_semester']) {

        if ($month > $currentMonth) {
            continue;
        }

    }

    $stmt = $conn->prepare("
        SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status='P' THEN 1 ELSE 0 END) as present
        FROM attendance
        WHERE student_id = ?
        AND semester = ?
        AND MONTH(date) = ?
    ");

    $stmt->bind_param("iii", $userId, $semesterNumber, $month);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res['total'] > 0) {
        $percent = round(($res['present'] / $res['total']) * 100);
    } else {
        $percent = 0;
    }

    $performanceTrend[] = $percent;
}

if (empty($performanceTrend)) {
    $performanceTrend = [];
}

$ct1Check = $conn->prepare("
SELECT COUNT(*) as cnt
FROM marks
WHERE student_id=? 
AND semester=? 
AND exam_type='CT1'
AND status='published'
");

$ct1Check->bind_param("ii", $userId, $semesterNumber);
$ct1Check->execute();
$ct1Row = $ct1Check->get_result()->fetch_assoc();

$ct1Published = $ct1Row['cnt'] > 0 ? 1 : 0;


$ct2Check = $conn->prepare("
SELECT COUNT(*) as cnt
FROM marks
WHERE student_id=? 
AND semester=? 
AND exam_type='CT2'
AND status='published'
");

$ct2Check->bind_param("ii", $userId, $semesterNumber);
$ct2Check->execute();
$ct2Row = $ct2Check->get_result()->fetch_assoc();

$ct2Published = $ct2Row['cnt'] > 0 ? 1 : 0;


/* =========================================
   7️⃣ SUBJECT-WISE PERFORMANCE
========================================= */
/* =========================================
   7️⃣ SUBJECT-WISE PERFORMANCE
========================================= */
$subjects = [];

if ($ct1Published == 1 && $ct2Published == 1) {

    $marksStmt = $conn->prepare("
        SELECT subject,
       SUM(obtained_marks) as totalObtained,
       SUM(total_marks) as totalMax
FROM marks
WHERE student_id = ?
AND semester = ?
GROUP BY subject
    ");

    $marksStmt->bind_param("ii", $userId, $semesterNumber);
    $marksStmt->execute();
    $marksRes = $marksStmt->get_result();

    while ($row = $marksRes->fetch_assoc()) {

        $percent = $row['totalMax'] > 0
            ? round(($row['totalObtained'] / $row['totalMax']) * 100, 2)
            : 0;

        $subjects[] = [
            "name" => $row['subject'],
            "obtained" => $row['totalObtained'],
            "total" => $row['totalMax'],
            "percent" => $percent
        ];
    }
}
/* =========================================
   8️⃣ Final Response
========================================= */

echo json_encode([
    "status" => true,
    "studentName" => $studentName,
    "rollNo" => $rollNo,
    "className" => $className,
    "semester" => $semesterNumber,
    "department" => $department,
    "presentDays" => $presentDays,
    "absentDays" => $absentDays,
    "performanceTrend" => $performanceTrend,
    "subjects" => $subjects
]);