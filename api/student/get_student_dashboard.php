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
   3️⃣ Check if CT1 + CT2 are fully published
========================================= */

$classPrefix = substr($className, 0, 4); // IF6K

$subStmt = $conn->prepare("
    SELECT COUNT(*) as totalSubjects
    FROM semester_subjects
    WHERE class = ?
    AND semester = ?
");

$subStmt->bind_param("si", $classPrefix, $semesterNumber);
$subStmt->execute();
$subRes = $subStmt->get_result()->fetch_assoc();

$totalSubjects = $subRes['totalSubjects'];

$pubStmt = $conn->prepare("
    SELECT COUNT(DISTINCT subject) as publishedSubjects
    FROM marks
    WHERE class = ?
    AND semester = ?
    AND exam_type IN ('CT1','CT2')
    AND status = 'published'
");

$pubStmt->bind_param("si", $className, $semesterNumber);
$pubStmt->execute();
$pubRes = $pubStmt->get_result()->fetch_assoc();

$publishedSubjects = $pubRes['publishedSubjects'];

$showSubjects = ($publishedSubjects == $totalSubjects);

/* =========================================
   4️⃣ CURRENT MONTH ATTENDANCE
========================================= */

$presentDays = 0;
$absentDays = 0;

$attStmt = $conn->prepare("
    SELECT status
    FROM attendance
    WHERE student_id = ?
    AND MONTH(date) = MONTH(CURRENT_DATE())
    AND YEAR(date) = YEAR(CURRENT_DATE())
");

$attStmt->bind_param("i", $userId);
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

    $year = ($month == 12) ? $currentYear - 1 : $currentYear;

    $stmt = $conn->prepare("
        SELECT status
        FROM attendance
        WHERE student_id = ?
        AND MONTH(date) = ?
        AND YEAR(date) = ?
    ");

    $stmt->bind_param("iii", $userId, $month, $year);
    $stmt->execute();
    $res = $stmt->get_result();

    $present = 0;
    $total = 0;

    while ($row = $res->fetch_assoc()) {

        $total++;

        if ($row['status'] == 'P' || $row['status'] == 'L') {
            $present++;
        }
    }

    $percent = $total > 0 ? round(($present / $total) * 100, 2) : 0;

    $performanceTrend[] = $percent;
}

/* =========================================
   7️⃣ SUBJECT-WISE PERFORMANCE
========================================= */

$subjects = [];

if ($showSubjects) {

    $marksStmt = $conn->prepare("
    SELECT subject,
           SUM(obtained_marks) as totalObtained,
           SUM(total_marks) as totalMax
    FROM marks
    WHERE student_id = ?
    AND class = ?
    AND semester = ?
    AND exam_type IN ('CT1','CT2')
    AND status = 'published'
    GROUP BY subject
");

    $marksStmt->bind_param("isi", $userId, $className, $semesterNumber);
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