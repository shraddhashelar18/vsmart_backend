<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
//get_hod_dashboard.php
require_once("../config.php");
require_once("../api_guard.php");
require_once("../promotion_helper.php");
require_once("../cors.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['department'])) {
    echo json_encode([
        "status" => false,
        "message" => "Department required"
    ]);
    exit;
}

$department = $data['department'];

/* GET ATKT LIMIT */
$setting = $conn->query("SELECT atkt_limit FROM settings LIMIT 1");
$atktLimit = (int)$setting->fetch_assoc()['atkt_limit'];

/* COUNT STUDENTS */
$stmt = $conn->prepare("
    SELECT user_id 
    FROM students 
    WHERE department = ?
");

$stmt->bind_param("s", $department);
$stmt->execute();
$result = $stmt->get_result();

$totalStudents = 0;
$promoted = 0;
$promotedWithBacklog = 0;
$detained = 0;

if (!$result) {
    echo json_encode([
        "status" => false,
        "message" => "Query failed"
    ]);
    exit;
}

while($row = $result->fetch_assoc()){

    $totalStudents++;

   $statusQuery = $conn->prepare("
    SELECT status FROM students WHERE user_id = ?
");

if (!$statusQuery) {
    die("Prepare failed: " . $conn->error);
}

$statusQuery->bind_param("i", $row['user_id']);
$statusQuery->execute();

$statusData = $statusQuery->get_result();

if (!$statusData || $statusData->num_rows == 0) {
    continue; // skip this student safely
}

$statusResult = $statusData->fetch_assoc();
$status = strtolower($statusResult['status']);

if ($status == "passed_out" || $status == "promoted") {
    $promoted++;
}
elseif ($status == "promoted_with_atkt") {
    $promotedWithBacklog++;
}
elseif ($status == "detained") {
    $detained++;
}
}

/* COUNT TEACHERS */

$teacherStmt = $conn->prepare("
SELECT COUNT(DISTINCT ta.user_id) AS totalTeachers
FROM teacher_assignments ta
JOIN teachers t ON t.user_id = ta.user_id
WHERE ta.department = ?
");

$teacherStmt->bind_param("s",$department);
$teacherStmt->execute();

$teacherResult = $teacherStmt->get_result();
$totalTeachers = $teacherResult->fetch_assoc()['totalTeachers'];

;

/* RESPONSE */

echo json_encode([
    "status" => true,
    "totalStudents" => (int)$totalStudents,
    "totalTeachers" => (int)$totalTeachers,
    "promoted" => (int)$promoted,
    "detained" => (int)$detained,
    "promotedWithBacklog" => (int)$promotedWithBacklog
]);

$stmt->close();
$teacherStmt->close();
$conn->close();

