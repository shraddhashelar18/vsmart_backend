<?php
require_once("../../config.php");
require_once("../../api_guard.php");
require_once("../../cors.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$phone = $data['phone'] ?? '';

if (empty($phone)) {
    echo json_encode([
        "status" => false,
        "message" => "Phone required"
    ]);
    exit;
}

$stmt = $conn->prepare("
SELECT 
    p.full_name AS name,
    p.mobile_no AS phone,
    p.enrollment_no,
    u.email
FROM parents p
LEFT JOIN users u ON p.user_id = u.user_id
WHERE p.mobile_no = ?
LIMIT 1
");

$stmt->bind_param("s", $phone);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    echo json_encode([
        "status" => false,
        "message" => "Parent not found"
    ]);
    exit;
}

$row = $res->fetch_assoc();

echo json_encode([
    "status" => true,
    "parent" => [
        "name" => $row['name'],
        "email" => $row['email'],
        "phone" => $row['phone'],
        "children" => [$row['enrollment_no']]
    ]
]);