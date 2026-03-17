<?php
//get_notifications.php
require_once("../config.php");
require_once("../api_guard.php");

header("Content-Type: application/json");

/* Allow only parent */
if ($currentRole != 'parent') {
    echo json_encode([
        "status" => false,
        "message" => "Access Denied"
    ]);
    exit;
}

$userId = $currentUserId;

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

$notifications = [];

while ($row = $result->fetch_assoc()) {

    $notifications[] = [
        "title" => "Message from " . $row['subject'],
        "message" => $row['message'],
        "date" => date("d M Y h:i A", strtotime($row['created_at']))
    ];
}

echo json_encode([
    "status" => true,
    "data" => $notifications
]);
?>