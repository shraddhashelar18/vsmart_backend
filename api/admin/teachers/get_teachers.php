<?php
require_once("../../config.php");
require_once("../../api_guard.php");
require_once("../../cors.php");

header("Content-Type: application/json");

if ($currentRole != "admin") {
    echo json_encode(["status" => false, "message" => "Access denied"]);
    exit;
}

// 🔥 FIX: get from POST body
$data = json_decode(file_get_contents("php://input"), true);
$department = $data['department'] ?? '';

// 🔥 SIMPLE QUERY (NO bind_param)
$sql = "
SELECT DISTINCT
    t.user_id,
    t.full_name,
    t.mobile_no,
    t.employee_id,
    u.email,
    c.class_name
FROM teacher_assignments ta

-- 🔥 department filter FIRST
JOIN teachers t ON t.user_id = ta.user_id
JOIN users u ON u.user_id = t.user_id

-- 🔥 for disable logic
LEFT JOIN classes c ON c.class_teacher = t.user_id

WHERE UPPER(TRIM(ta.department)) = UPPER(TRIM('$department'))
";
$result = $conn->query($sql);

if (!$result) {
    echo json_encode([
        "status" => false,
        "error" => $conn->error
    ]);
    exit;
}

$teachers = [];

while ($row = $result->fetch_assoc()) {
    $teachers[] = [
        "id" => intval($row["user_id"]),
        "name" => $row["full_name"],
        "email" => $row["email"],
        "phone" => $row["mobile_no"],
        "employee_id" => $row["employee_id"],
        "class_name" => $row["class_name"] ?? ""
    ];
}

echo json_encode([
    "status" => true,
    "teachers" => $teachers
]);