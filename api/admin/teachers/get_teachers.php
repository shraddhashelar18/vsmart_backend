<?php
//add teacher.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once(__DIR__ . "/../../cors.php");
require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../api_guard.php");

header("Content-Type: application/json");
if($currentRole != "admin"){
    echo json_encode(["status"=>false,"message"=>"Access denied"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$department = $data['department'] ?? '';

if (empty($department)) {
    echo json_encode([
        "status" => false,
        "message" => "Department required"
    ]);
    exit;
}

$stmt = $conn->prepare("
SELECT DISTINCT
    t.user_id AS id,
    t.full_name AS name,
    u.email,
    t.mobile_no AS phone,
    t.employee_id,
    c.class_name
FROM teacher_assignments ta
JOIN teachers t ON t.user_id = ta.user_id
JOIN users u ON u.user_id = t.user_id
LEFT JOIN classes c ON c.class_teacher = t.user_id
WHERE ta.department = ?
ORDER BY t.full_name ASC
");

$stmt->bind_param("s", $department);
$stmt->execute();

$result = $stmt->get_result();

$teachers = [];

while ($row = $result->fetch_assoc()) {

    $teachers[] = [
        "id" => intval($row["id"]),
        "name" => $row["name"],
        "email" => $row["email"],
        "phone" => $row["phone"],
        "employee_id" => $row["employee_id"] ?? "",
        "departments" => [$department],
        "class_name" => $row["class_name"] ?? ""
    ];
}
echo json_encode([
    "status" => true,
    "teachers" => $teachers
]);