<?php

require_once(__DIR__ . "/../config.php");
require_once(__DIR__ . "/../api_guard.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$department = $data['department'] ?? '';

if (empty($department)) {
    echo json_encode([
        "status" => false,
        "message" => "Department required"
    ]);
    exit;
}

/* ===============================
   GET TEACHERS BY DEPARTMENT
================================= */

$stmt = $conn->prepare("
    SELECT 
        t.user_id AS id,
        t.full_name AS name,
        u.email,
        t.mobile_no AS phone
    FROM teacher_assignments ta
    JOIN teachers t ON t.user_id = ta.user_id
    JOIN users u ON u.user_id = t.user_id
    WHERE ta.department_code = ?
    GROUP BY t.user_id
");

$stmt->bind_param("s", $department);
$stmt->execute();

$result = $stmt->get_result();

$teachers = [];

while ($row = $result->fetch_assoc()) {
    $row["departments"] = [$department];
    $teachers[] = $row;
}

echo json_encode([
    "status" => true,
    "teachers" => $teachers
]);