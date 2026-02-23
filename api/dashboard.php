<?php
require_once("config.php");
require_once("api_guard.php");

header("Content-Type: application/json");

// ✅ Allow only POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => false,
        "message" => "Only POST method allowed"
    ]);
    exit;
}

// ✅ Get JSON body
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['user_id'])) {
    echo json_encode([
        "status" => false,
        "message" => "User ID required"
    ]);
    exit;
}

$user_id = intval($data['user_id']);

if ($user_id <= 0) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid User ID"
    ]);
    exit;
}

/* ============================= */
/* GET HOD DEPARTMENT */
/* ============================= */

$stmt = $conn->prepare("SELECT department FROM hods WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode([
        "status" => false,
        "message" => "HOD not found"
    ]);
    exit;
}

$hod = $result->fetch_assoc();
$department = $hod['department'];

/* Prefix Logic */
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
$stmt2->bind_param("s", $department);
$stmt2->execute();
$total_teachers = $stmt2->get_result()->fetch_assoc()['total'] ?? 0;


/* ============================= */
/* PROMOTED */
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
/* DETAINED */
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

exit;
