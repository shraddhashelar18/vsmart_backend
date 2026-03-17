<?php
require_once("../config.php");
require_once "../cors.php"; 
require_once("../api_guard.php");

header("Content-Type: application/json");

/* Allow Only Student */
if ($currentRole != 'student') {
    echo json_encode([
        "status" => false,
        "message" => "Access Denied"
    ]);
    exit;
}

$userId = $currentUserId;   // student user_id
// If user_id is passed in body (for testing)
$input = json_decode(file_get_contents("php://input"), true);

if (isset($input['user_id']) && is_numeric($input['user_id'])) {
    $userId = (int) $input['user_id'];
}
if (!$conn) {
    echo json_encode([
        "status" => false,
        "message" => "Database connection error"
    ]);
    exit;
}
$checkUser = $conn->prepare("SELECT user_id FROM students WHERE user_id = ?");
$checkUser->bind_param("i", $userId);
$checkUser->execute();
$checkRes = $checkUser->get_result();

if ($checkRes->num_rows == 0) {
    echo json_encode([
        "status" => false,
        "message" => "Student not found"
    ]);
    exit;
}
/* Fetch Notifications */

$stmt = $conn->prepare("
    SELECT n.subject, n.message, n.created_at
    FROM notifications n
    INNER JOIN notification_receivers nr 
        ON n.id = nr.notification_id
    WHERE nr.receiver_user_id = ?
    ORDER BY n.created_at DESC
");

$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if (!$result) {
    echo json_encode([
        "status" => false,
        "message" => "Failed to fetch notifications"
    ]);
    exit;
}
$notifications = [];

while ($row = $result->fetch_assoc()) {

    $notifications[] = [
        "title" => $row['subject'],  // Flutter expects title
        "message" => $row['message'],
        "date" => date("d M Y h:i A", strtotime($row['created_at']))
    ];
}

echo json_encode([
    "status" => true,
    "data" => $notifications ?? []
]);