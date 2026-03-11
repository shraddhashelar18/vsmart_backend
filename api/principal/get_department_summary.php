<?php
require_once("../config.php");
require_once("../api_guard.php");

header("Content-Type: application/json");

/* 🔐 Role Check */
if ( $currentRole != 'principal') {
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
   1️⃣ Get Classes Of Department
================================ */

$classQuery = $conn->prepare("
    SELECT DISTINCT class
    FROM students
    WHERE department = ?
");

$classQuery->bind_param("s", $department);
$classQuery->execute();
$classResult = $classQuery->get_result();

$classes = [];

while ($row = $classResult->fetch_assoc()) {
    $classes[] = $row['class'];
}

/* ===============================
   2️⃣ Student Summary Class Wise
================================ */

$classSummary = [];

foreach ($classes as $class) {

    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as totalStudents,
            SUM(status='promoted') as promoted,
            SUM(status='promoted_with_atkt') as promotedWithBacklog,
            SUM(status='detained') as detained
        FROM students
        WHERE class = ? AND department = ?
    ");

    $stmt->bind_param("ss", $class, $department);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $classSummary[] = [
        "class" => $class,
        "totalStudents" => (int)$row['totalStudents'],
        "promoted" => (int)$row['promoted'],
        "promotedWithBacklog" => (int)$row['promotedWithBacklog'],
        "detained" => (int)$row['detained']
    ];
}

/* ===============================
   3️⃣ Total Teachers
================================ */

$teacherQuery = $conn->prepare("
    SELECT COUNT(*) as totalTeachers
    FROM teachers
");

$teacherQuery->execute();
$teacherResult = $teacherQuery->get_result();
$totalTeachers = $teacherResult->fetch_assoc()['totalTeachers'] ?? 0;

/* ===============================
   4️⃣ Total Students
================================ */

$studentQuery = $conn->prepare("
    SELECT COUNT(*) as totalStudents
    FROM students
    WHERE department = ?
");

$studentQuery->bind_param("s", $department);
$studentQuery->execute();
$studentResult = $studentQuery->get_result();
$totalStudents = $studentResult->fetch_assoc()['totalStudents'] ?? 0;

/* ===============================
   5️⃣ Final Response
================================ */

echo json_encode([
    "status" => true,
    "department" => $department,
    "totalStudents" => (int)$totalStudents,
    "totalTeachers" => (int)$totalTeachers,
    "classes" => $classSummary
]);