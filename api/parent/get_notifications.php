<?php
header("Content-Type: application/json");
require_once "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['class'])) {
    echo json_encode(["status" => false]);
    exit;
}

$class = $data['class'];

$query = $conn->prepare("SELECT subject, message, created_at FROM notifications WHERE class=? ORDER BY created_at DESC");
$query->bind_param("s", $class);
$query->execute();
$result = $query->get_result();

$notifications = [];

while ($row = $result->fetch_assoc()) {
    $notifications[] = [
        "title" => "New Message from " . $row['subject'],
        "message" => $row['message'],
        "date" => $row['created_at']
    ];
}

echo json_encode([
    "status" => true,
    "notifications" => $notifications
]);
?>