<?php
require_once("../config.php");
require_once("../api_guard.php");
require_once("../promotion_helper.php");

header("Content-Type: application/json");

/* 🔐 Role Check */
if ($currentRole != 'hod' && $currentRole != 'principal') {
    echo json_encode([
        "status" => false,
        "message" => "Access Denied"
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

/* 🎯 Department Logic */
if ($currentRole == 'hod') {
    $department = $currentDepartment; // from api_guard
} else {
    if (!isset($data['department'])) {
        echo json_encode([
            "status" => false,
            "message" => "Department required"
        ]);
        exit;
    }
    $department = $data['department'];
}

/* ===============================
   1️⃣ Get ATKT Limit
================================ */

$setting = $conn->query("SELECT atkt_limit FROM settings LIMIT 1");
$atktLimit = $setting->fetch_assoc()['atkt_limit'] ?? 2;

/* ===============================
   2️⃣ Get Classes Of Department
================================ */

$classQuery = $conn->prepare("
    SELECT DISTINCT class
    FROM teacher_assignments
    WHERE department = ?
    AND status = 'active'
");

$classQuery->bind_param("s", $department);
$classQuery->execute();
$classResult = $classQuery->get_result();

$classes = [];

while ($row = $classResult->fetch_assoc()) {
    $classes[] = $row['class'];
}

/* ===============================
   3️⃣ Calculate Student Summary
================================ */

$totalStudents = 0;
$promoted = 0;
$promotedWithBacklog = 0;
$detained = 0;

foreach ($classes as $class) {

    $stmt = $conn->prepare("
        SELECT user_id
        FROM students
        WHERE class = ?
    ");

    $stmt->bind_param("s", $class);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($student = $result->fetch_assoc()) {

        $totalStudents++;

        $promotion = calculatePromotion(
            $conn,
            $student['user_id'],
            $atktLimit
        );

        if ($promotion['status'] == "PROMOTED")
            $promoted++;

        elseif ($promotion['status'] == "PROMOTED_WITH_ATKT")
            $promotedWithBacklog++;

        elseif ($promotion['status'] == "DETAINED")
            $detained++;
    }
}

/* ===============================
   4️⃣ Count Teachers
================================ */

$teacherQuery = $conn->prepare("
    SELECT COUNT(DISTINCT user_id) as total
    FROM teacher_assignments
    WHERE department = ?
    AND status = 'active'
");

$teacherQuery->bind_param("s", $department);
$teacherQuery->execute();
$teacherResult = $teacherQuery->get_result();
$totalTeachers = $teacherResult->fetch_assoc()['total'] ?? 0;

/* ===============================
   5️⃣ Final Response
================================ */

echo json_encode([
    "status" => true,
    "totalStudents" => $totalStudents,
    "totalTeachers" => (int)$totalTeachers,
    "promoted" => $promoted,
    "promotedWithBacklog" => $promotedWithBacklog,
    "detained" => $detained
]);