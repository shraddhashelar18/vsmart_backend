<?php
require_once("../config.php");
require_once("../api_guard.php");
require_once("../promotion_helper.php");
if ($currentRole != 'hod' && $currentRole != 'principal') {
    echo json_encode([
        "status" => false,
        "message" => "Access Denied"
    ]);
    exit;
}
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['class']) || empty(trim($data['class']))) {
    echo json_encode([
        "status" => false,
        "message" => "Class is required"
    ]);
    exit;
}
if (!preg_match('/^[A-Z]{2}[0-9][A-Z]{2}$/', $class)) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid class format"
    ]);
    exit;
}

$class = trim($data['class']);

// Get ATKT limit
$setting = $conn->query("SELECT atkt_limit FROM settings LIMIT 1");
$atktLimit = $setting->fetch_assoc()['atkt_limit'];

// Get students of class
$stmt = $conn->prepare("
    SELECT user_id, full_name
    FROM students
    WHERE class = ?
");
$stmt->bind_param("s", $class);
$stmt->execute();
$result = $stmt->get_result();

$students = [];

while ($row = $result->fetch_assoc()) {

    $promotion = calculatePromotion($conn, $row['user_id'], $atktLimit);

    if ($promotion['status'] == "PROMOTED") {

        $students[] = [
            "name" => $row['full_name'],
            "backlogCount" => $promotion['backlogCount'],
            "promotionStatus" => $promotion['status'],
            "ktSubjects" => $promotion['ktSubjects']
        ];
    }
}

echo json_encode([
    "status" => true,
    "students" => $students
]);