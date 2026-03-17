<?php
require_once("../config.php");
require_once "../cors.php";
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

$userId = $currentUserId;   // from token

/* =========================================
   2️⃣ Fetch Student Details
========================================= */

$stmt = $conn->prepare("
    SELECT full_name, roll_no, class, current_semester, department_code
    FROM students
    WHERE user_id = ?
");

$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    echo json_encode([
        "status" => false,
        "message" => "Student not found"
    ]);
    exit;
}

$student = $res->fetch_assoc();

$studentName = $student['full_name'];
$rollNo = $student['roll_no'];
$className = $student['class'];
$semesterCode = $student['current_semester']; // SEM6
$department = $student['department_code'];

/* Extract number (6) */
$semesterNumber = (int) filter_var($semesterCode, FILTER_SANITIZE_NUMBER_INT);
/* 🔹 Validate Semester Format */
if ($semesterNumber <= 0) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid semester"
    ]);
    exit;
}
/* =========================================
   3️⃣ CURRENT MONTH ATTENDANCE
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
if (!$attRes) {
    echo json_encode([
        "status" => false,
        "message" => "Attendance fetch error"
    ]);
    exit;
}
while ($row = $attRes->fetch_assoc()) {

    if ($row['status'] == 'present' || $row['status'] == 'late') {
        $presentDays++;
    }

    if ($row['status'] == 'absent') {
        $absentDays++;
    }
}

/* =========================================
   4️⃣ PERFORMANCE TREND (Last 6 Months)
========================================= */

$$performanceTrend = [];

for ($i = 5; $i >= 0; $i--) {

    $month = date("m", strtotime("-$i months"));
    $year = date("Y", strtotime("-$i months"));

    $trendStmt = $conn->prepare("
        SELECT status
        FROM attendance
        WHERE student_id = ?
        AND MONTH(date) = ?
        AND YEAR(date) = ?
    ");

    $trendStmt->bind_param("iii", $userId, $month, $year);
    $trendStmt->execute();
    $trendRes = $trendStmt->get_result();

    $present = 0;

    while ($r = $trendRes->fetch_assoc()) {

        if ($r['status'] == 'present' || $r['status'] == 'late') {
            $present++;
        }
    }

    $performanceTrend[] = $present;
}
/* =========================================
   5️⃣ SUBJECT-WISE PERFORMANCE (CT1 + CT2)
   Total CT per subject = 60
========================================= */

$subjects = [];

$marksStmt = $conn->prepare("
    SELECT subject,
           SUM(obtained_marks) as totalObtained,
           SUM(total_marks) as totalMax
    FROM marks
    WHERE student_id = ?
    AND semester = ?
    AND exam_type IN ('CT-1', 'CT-2')
    GROUP BY subject
");

$marksStmt->bind_param("is", $userId, $semesterCode);
$marksStmt->execute();

$marksRes = $marksStmt->get_result();
if (!$marksRes) {
    echo json_encode([
        "status" => false,
        "message" => "Marks fetch error"
    ]);
    exit;
}
while ($row = $marksRes->fetch_assoc()) {

    $percent = $row['totalMax'] > 0
        ? round(($row['totalObtained'] / $row['totalMax']) * 100, 2)
        : 0;

    $subjects[] = [
        "name" => $row['subject'],
        "percent" => $percent
    ];
}

/* =========================================
   6️⃣ Final Response (Matches Flutter)
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