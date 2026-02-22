<?php

/* =====================================
   IMPORT REQUIRED FILES
===================================== */

require_once("../config.php");
require_once("../api_guard.php");

/* =====================================
   SET RESPONSE TYPE
===================================== */

header("Content-Type: application/json");

/* =====================================
   GET JSON BODY
===================================== */

$data = json_decode(file_get_contents("php://input"), true);

$className = $data['class'] ?? '';

if (empty($className)) {
    echo json_encode([
        "status" => false,
        "message" => "Class required"
    ]);
    exit;
}

/* =====================================
   FETCH STUDENTS
===================================== */

$stmt = $conn->prepare("
    SELECT 
        s.enrollment_no AS enrollment,
        s.full_name AS name,
        u.email AS email,
        s.mobile_no AS phone,
        s.parent_mobile_no AS parentPhone,
        s.roll_no AS roll,
        s.class
    FROM students s
    LEFT JOIN users u ON u.user_id = s.user_id
    WHERE s.class = ?
");

$stmt->bind_param("s", $className);
$stmt->execute();
$res = $stmt->get_result();

$students = [];

while ($row = $res->fetch_assoc()) {
    $students[] = $row;
}

/* =====================================
   RESPONSE
===================================== */

echo json_encode([
    "status" => true,
    "students" => $students
]);