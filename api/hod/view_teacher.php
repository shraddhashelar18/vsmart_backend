<?php
require_once("../config.php");
require_once("../api_guard.php");

header("Content-Type: application/json");

/* ========================= */
/* READ JSON BODY */
/* ========================= */

$data = json_decode(file_get_contents("php://input"), true);

$user_id = intval($data['user_id'] ?? 0);

if ($user_id <= 0) {
    echo json_encode([
        "status" => false,
        "message" => "User ID required"
    ]);
    exit;
}

/* ========================= */
/* GET HOD DEPARTMENT */
/* ========================= */

$stmt = $conn->prepare("SELECT department FROM hod WHERE user_id = ?");
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

/* ========================= */
/* GET TEACHERS FROM ASSIGNMENTS */
/* ========================= */

$stmt2 = $conn->prepare("
    SELECT DISTINCT 
        t.user_id,
        t.employee_id,
        t.full_name,
        u.email,
        ta.class,
        ta.subject
    FROM teacher_assignments ta
    INNER JOIN teachers t ON t.user_id = ta.user_id
    INNER JOIN users u ON u.user_id = t.user_id
    WHERE ta.department_code = ?
    AND ta.status = 'active'
");

$stmt2->bind_param("s", $department);
$stmt2->execute();
$teachers_result = $stmt2->get_result();

$teachers = [];

while ($row = $teachers_result->fetch_assoc()) {
    $teachers[] = $row;
}

/* ========================= */
/* FINAL RESPONSE */
/* ========================= */

echo json_encode([
    "status" => true,
    "department" => $department,
    "total_teachers" => count($teachers),
    "teachers" => $teachers
]);