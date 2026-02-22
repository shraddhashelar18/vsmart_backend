<?php

require_once(__DIR__ . "/../config.php");
require_once(__DIR__ . "/../api_guard.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$id = intval($data['id'] ?? 0);

if ($id <= 0) {
    echo json_encode([
        "status" => false,
        "message" => "Teacher ID required"
    ]);
    exit;
}

/* ===============================
   GET BASIC TEACHER INFO
================================= */

$stmt = $conn->prepare("
    SELECT 
        t.user_id AS id,
        t.full_name AS name,
        u.email,
        t.mobile_no AS phone
    FROM teachers t
    JOIN users u ON u.user_id = t.user_id
    WHERE t.user_id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode([
        "status" => false,
        "message" => "Teacher not found"
    ]);
    exit;
}

$teacher = $result->fetch_assoc();

/* ===============================
   GET CLASSES + SUBJECTS
================================= */

$stmt2 = $conn->prepare("
    SELECT class, subject, department_code
    FROM teacher_assignments
    WHERE user_id = ? AND status = 'active'
");

$stmt2->bind_param("i", $id);
$stmt2->execute();
$res2 = $stmt2->get_result();

$classes = [];
$subjects = [];
$departments = [];

while ($row = $res2->fetch_assoc()) {
    $classes[] = $row['class'];
    $departments[] = $row['department_code'];

    $subjects[$row['class']][] = $row['subject'];
}

/* Remove duplicates */
$classes = array_values(array_unique($classes));
$departments = array_values(array_unique($departments));

echo json_encode([
    "status" => true,
    "id" => $teacher['id'],
    "name" => $teacher['name'],
    "email" => $teacher['email'],
    "phone" => $teacher['phone'],
    "departments" => $departments,
    "classes" => $classes,
    "subjects" => $subjects
]);