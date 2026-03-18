<?php
//get_subjects.php

require_once "../config.php";
require_once "../cors.php"; 
require_once "../api_guard.php"; // ✅ ADDED

header("Content-Type: application/json");

/* ================= ROLE CHECK ================= */
if ($currentRole != 'parent') {
    echo json_encode([
        "status" => false,
        "message" => "Access Denied"
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['class']) || !isset($data['semester'])) {
    echo json_encode(["status" => false, "message" => "class and semester required"]);
    exit;
}

$class = $data['class'];
$semester = $data['semester'];

/* Convert IF6KA → IF6K (remove division letter) */
$baseClass = strlen($class) > 4 ? substr($class, 0, -1) : $class;

$query = $conn->prepare("
SELECT subject_name
FROM semester_subjects
WHERE class=? AND semester=?
ORDER BY subject_name
");

$query->bind_param("ss", $baseClass, $semester);
$query->execute();

$result = $query->get_result();
$subjects = [];

while ($row = $result->fetch_assoc()) {
    $subjects[] = $row['subject_name'];
}

echo json_encode([
    "status" => true,
    "subjects" => $subjects
]);
?>