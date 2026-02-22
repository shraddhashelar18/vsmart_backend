<?php

require_once("../config.php");
require_once("../api_guard.php");

header("Content-Type: application/json");

/* ===========================
   READ JSON INPUT
=========================== */

$data = json_decode(file_get_contents("php://input"), true);

$id       = intval($data['id'] ?? 0);
$name     = $data['name'] ?? '';
$phone    = $data['phone'] ?? '';
$subjects = $data['subjects'] ?? [];

/* ===========================
   VALIDATION
=========================== */

if ($id <= 0) {
    echo json_encode([
        "status" => false,
        "message" => "Teacher ID required"
    ]);
    exit;
}

if (empty($name)) {
    echo json_encode([
        "status" => false,
        "message" => "Teacher name required"
    ]);
    exit;
}

/* ===========================
   UPDATE TEACHER BASIC INFO
   (Email NOT updated)
=========================== */

$stmt = $conn->prepare("
    UPDATE teachers
    SET full_name = ?, mobile_no = ?
    WHERE user_id = ?
");

$stmt->bind_param("ssi", $name, $phone, $id);
$stmt->execute();

/* ===========================
   DELETE OLD ASSIGNMENTS
=========================== */

$deleteStmt = $conn->prepare("
    DELETE FROM teacher_assignments
    WHERE user_id = ?
");

$deleteStmt->bind_param("i", $id);
$deleteStmt->execute();

/* ===========================
   INSERT NEW ASSIGNMENTS
=========================== */

foreach ($subjects as $class => $subjectList) {

    foreach ($subjectList as $subject) {

        // Department derived from class prefix
        $department_code = substr($class, 0, 2); // IF, CO, EJ

        $insertStmt = $conn->prepare("
            INSERT INTO teacher_assignments
            (user_id, department_code, class, subject, status)
            VALUES (?, ?, ?, ?, 'active')
        ");

        $insertStmt->bind_param("isss", $id, $department_code, $class, $subject);
        $insertStmt->execute();
    }
}

/* ===========================
   FINAL RESPONSE
=========================== */

echo json_encode([
    "status" => true,
    "message" => "Teacher updated successfully"
]);