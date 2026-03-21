<?php
require_once("../config.php");
require_once("../api_guard.php");
require_once("../cors.php");

$student_id = $_GET['user_id'];
$semester = $_GET['semester'];

$response = [];

/* ================= ROLE CHECK ================= */
if ($currentRole != 'student') {
    echo json_encode([
        "status" => false,
        "message" => "Access Denied"
    ]);
    exit;
}
/* ================= DISPLAY SEM LOGIC ================= */

$display_semester = $semester;

// ✅ FIX: added status='published'
$check = $conn->query("
SELECT COUNT(*) as total 
FROM marks 
WHERE student_id='$student_id' 
AND semester='$semester' 
AND exam_type='CT1'
AND status='published'
")->fetch_assoc();

$ct1_exists = $check['total'] > 0;

// fallback to previous sem
if (!$ct1_exists && $semester > 1) {
    $display_semester = $semester - 1;
}

/* ================= CLASS NAME ================= */

$student = $conn->query("
SELECT department 
FROM students 
WHERE user_id='$student_id'
")->fetch_assoc();

$dept = $student['department'];

$className = $dept . $display_semester . "KA";

$response["className"] = $className;
$response["semester"] = $display_semester;


/* ================= PREVIOUS SEM LOGIC ================= */

$previous_sem = null;

// ✅ FIX: only when CT1 is published
if ($ct1_exists && $display_semester > 1) {
    $previous_sem = $display_semester - 1;
}

# RESULT SUMMARY

$result = $conn->query("
SELECT percentage, marksheet_pdf
FROM semester_results
WHERE student_id='$student_id'
AND semester='$display_semester'
")->fetch_assoc();

if ($result) {

    $response["percentage"] = $result["percentage"];
    if (!empty($result["marksheet_pdf"])) {

        $path = $result["marksheet_pdf"];

        $path = str_replace("uploads/marksheets/uploads/marksheets/", "uploads/marksheets/", $path);

        if (!str_contains($path, "uploads/marksheets/")) {
            $path = "uploads/marksheets/" . $path;
        }

        $response["marksheetPdf"] = BASE_URL . $path;

    } else {
        $response["marksheetPdf"] = null;
    }

    $response["status"] = ($result["percentage"] >= 40) ? "PASS" : "FAIL";

} else {

    $response["percentage"] = 0;
    $response["marksheetPdf"] = null;
    $response["status"] = "RESULT_NOT_DECLARED";

}

# ATTENDANCE %

$att = $conn->query("
SELECT status
FROM attendance
WHERE student_id='$student_id'
AND semester='$display_semester'
");

$total = $att->num_rows;
$present = 0;

while ($r = $att->fetch_assoc()) {
    if ($r['status'] == "P")
        $present++;
}

$attendancePercent = 0;

if ($total > 0) {
    $attendancePercent = ($present / $total) * 100;
}

$response["attendance"] = round($attendancePercent);

# ATTENDANCE TREND

$trendQuery = $conn->query("
SELECT 
DATE_FORMAT(date,'%M') as month,
SUM(CASE WHEN status='P' THEN 1 ELSE 0 END) as present,
COUNT(*) as total
FROM attendance
WHERE student_id='$student_id'
AND semester='$display_semester'
GROUP BY MONTH(date)
ORDER BY MONTH(date)
");

$trend = new stdClass();

while ($t = $trendQuery->fetch_assoc()) {
    $month = $t['month'];
    $trend->$month = round(($t['present'] / $t['total']) * 100, 2);
}

$response["attendanceTrend"] = $trend;

# MARKS

$marksQuery = $conn->query("
SELECT subject,exam_type,obtained_marks
FROM marks
WHERE student_id='$student_id'
AND semester='$display_semester'
AND status='published'
");

$subjects = [];

while ($row = $marksQuery->fetch_assoc()) {

    $subject = $row['subject'];
    $exam = $row['exam_type'];

    if (!isset($subjects[$subject])) {
        $subjects[$subject] = [
            "CT1" => 0,
            "CT2" => 0,
            "FINAL" => 0
        ];
    }

    $subjects[$subject][$exam] = $row['obtained_marks'] ?? 0;
}

$response["marks"] = $subjects;

# ================= PREVIOUS SEM DATA =================

$previousData = null;

if ($previous_sem && $previous_sem >= 1) {

    $prevResult = $conn->query("
    SELECT percentage, marksheet_pdf
    FROM semester_results
    WHERE student_id='$student_id'
    AND semester='$previous_sem'
    ")->fetch_assoc();

    if ($prevResult) {

        $prev = [];

        $prev["percentage"] = $prevResult["percentage"];
        $prev["status"] = ($prevResult["percentage"] >= 40) ? "PASS" : "FAIL";

        if (!empty($prevResult["marksheet_pdf"])) {

            $path = $prevResult["marksheet_pdf"];

            $path = str_replace("uploads/marksheets/uploads/marksheets/", "uploads/marksheets/", $path);

            if (!str_contains($path, "uploads/marksheets/")) {
                $path = "uploads/marksheets/" . $path;
            }

            $prev["marksheetPdf"] = BASE_URL . $path;

        } else {
            $prev["marksheetPdf"] = null;
        }

        $attPrev = $conn->query("
        SELECT status FROM attendance
        WHERE student_id='$student_id'
        AND semester='$previous_sem'
        ");

        $total = $attPrev->num_rows;
        $present = 0;

        while ($r = $attPrev->fetch_assoc()) {
            if ($r['status'] == "P")
                $present++;
        }

        $prev["attendance"] = $total > 0 ? round(($present / $total) * 100) : 0;

        $trendQuery = $conn->query("
        SELECT 
        DATE_FORMAT(date,'%M') as month,
        SUM(CASE WHEN status='P' THEN 1 ELSE 0 END) as present,
        COUNT(*) as total
        FROM attendance
        WHERE student_id='$student_id'
        AND semester='$previous_sem'
        GROUP BY MONTH(date)
        ORDER BY MONTH(date)
        ");

        $trendPrev = new stdClass();

        while ($t = $trendQuery->fetch_assoc()) {
            $month = $t['month'];
            $trendPrev->$month = round(($t['present'] / $t['total']) * 100, 2);
        }

        $prev["attendanceTrend"] = $trendPrev;

        $marksQuery = $conn->query("
        SELECT subject,exam_type,obtained_marks
        FROM marks
        WHERE student_id='$student_id'
        AND semester='$previous_sem'
        AND status='published'
        ");

        $subjects = [];

        while ($row = $marksQuery->fetch_assoc()) {

            $subject = $row['subject'];
            $exam = $row['exam_type'];

            if (!isset($subjects[$subject])) {
                $subjects[$subject] = [
                    "CT1" => 0,
                    "CT2" => 0,
                    "FINAL" => 0
                ];
            }

            $subjects[$subject][$exam] = $row['obtained_marks'] ?? 0;
        }

        $prev["marks"] = $subjects;

        $previousData = $prev;
    }
}

echo json_encode([
    "status" => true,
    "data" => $response,
    "previous_semester" => $previousData
]);
?>