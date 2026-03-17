<?php
require_once("../config.php");
require_once "../cors.php"; 
require_once("../api_guard.php");

header("Content-Type: application/json");

/* 🔐 Role Check */
if ($currentRole != 'principal') {
    echo json_encode([
        "status" => false,
        "message" => "Access Denied"
    ]);
    exit;
}

/* Get distinct departments */
$result = $conn->query("
    SELECT DISTINCT department
    FROM teacher_assignments
    WHERE status = 'active'
    ORDER BY department
");

$departments = [];

while ($row = $result->fetch_assoc()) {
    $departments[] = $row['department'];
}

echo json_encode([
    "status" => true,
    "departments" => $departments
]);