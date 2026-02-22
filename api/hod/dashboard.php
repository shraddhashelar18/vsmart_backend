<?php
require_once("../config.php");
require_once("../api_guard.php");

header("Content-Type: application/json");

/* ============================= */
/* READ JSON INPUT */
/* ============================= */

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data['user_id'] ?? '';

if($user_id == ''){
    echo json_encode([
        "status" => false,
        "message" => "User ID required"
    ]);
    exit;
}

/* ============================= */
/* STEP 1: GET HOD DEPARTMENT */
/* ============================= */

$stmt = $conn->prepare("SELECT department FROM hod WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    echo json_encode([
        "status" => false,
        "message" => "HOD not found"
    ]);
    exit;
}

$hod = $result->fetch_assoc();
$department = $hod['department'];

/* ============================= */
/* STEP 2: PREFIX LOGIC */
/* ============================= */

$prefix = ($department == 'IT') ? 'IF' : $department;

/* ============================= */
/* TOTAL STUDENTS */
/* ============================= */

$stmt1 = $conn->prepare("
    SELECT COUNT(*) as total
    FROM students
    WHERE class LIKE CONCAT(?, '%')
");
$stmt1->bind_param("s", $prefix);
$stmt1->execute();
$total_students = $stmt1->get_result()->fetch_assoc()['total'] ?? 0;

/* ============================= */
/* TOTAL TEACHERS */
/* ============================= */

$stmt2 = $conn->prepare("
    SELECT COUNT(DISTINCT user_id) as total
    FROM teacher_assignments
    WHERE department_code = ?
    AND status = 'active'
");
$stmt2->bind_param("s", $prefix);
$stmt2->execute();
$total_teachers = $stmt2->get_result()->fetch_assoc()['total'] ?? 0;

/* ============================= */
/* PROMOTED (percentage >= 40) */
/* ============================= */

$stmt3 = $conn->prepare("
    SELECT COUNT(*) as total
    FROM semester_results sr
    JOIN students s ON s.user_id = sr.student_id
    WHERE s.class LIKE CONCAT(?, '%')
    AND sr.percentage >= 40
");
$stmt3->bind_param("s", $prefix);
$stmt3->execute();
$promoted = $stmt3->get_result()->fetch_assoc()['total'] ?? 0;

/* ============================= */
/* DETAINED (percentage < 40) */
/* ============================= */

$stmt4 = $conn->prepare("
    SELECT COUNT(*) as total
    FROM semester_results sr
    JOIN students s ON s.user_id = sr.student_id
    WHERE s.class LIKE CONCAT(?, '%')
    AND sr.percentage < 40
");
$stmt4->bind_param("s", $prefix);
$stmt4->execute();
$detained = $stmt4->get_result()->fetch_assoc()['total'] ?? 0;

/* ============================= */
/* FINAL RESPONSE */
/* ============================= */

echo json_encode([
    "status" => true,
    "department" => $department,
    "total_students" => intval($total_students),
    "total_teachers" => intval($total_teachers),
    "promoted" => intval($promoted),
    "detained" => intval($detained)
]);