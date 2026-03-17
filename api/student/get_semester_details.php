<?php
require_once("../config.php");
require_once "../cors.php"; 
require_once("../api_guard.php");

/* ================= ROLE CHECK ================= */
if ($currentRole != 'student') {
    echo json_encode([
        "status" => false,
        "message" => "Access Denied"
    ]);
    exit;
}

$student_id = $_GET['user_id'];
$semester = $_GET['semester'];

$response = [];

# RESULT SUMMARY

$result = $conn->query("
SELECT percentage, marksheet_pdf
FROM semester_results
WHERE student_id='$student_id'
AND semester='$semester'
")->fetch_assoc();

if ($result) {

    $response["percentage"] = $result["percentage"];

    if (!empty($result["marksheet_pdf"])) {
        $response["marksheetPdf"] =
            "http://192.168.0.102:8080/vsmart_backend/uploads/marksheets/" . $result["marksheet_pdf"];
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
AND semester='$semester'
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
AND semester='$semester'
GROUP BY MONTH(date)
ORDER BY MONTH(date)
");

$trend = [];

while ($t = $trendQuery->fetch_assoc()) {

    $month = $t['month'];

    $trend[$month] = round(($t['present'] / $t['total']) * 100, 2);

}

$response["attendanceTrend"] = $trend;

# MARKS

$marksQuery = $conn->query("
SELECT subject,exam_type,obtained_marks
FROM marks
WHERE student_id='$student_id'
AND semester='$semester'
AND status='published'
");

$subjects = [];

while ($row = $marksQuery->fetch_assoc()) {

    $subject = $row['subject'];
    $exam = $row['exam_type'];

    $subjects[$subject][$exam] = $row['obtained_marks'];

}

$response["marks"] = $subjects;

echo json_encode([
    "status" => true,
    "data" => $response
]);
?>