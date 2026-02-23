<?php

require_once "config.php";

/* ===============================
   GET INPUT
=================================*/
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['action'])) {
    echo json_encode(["status" => false, "message" => "Action required"]);
    exit();
}

$action = $data['action'];


/* =====================================================
   1️⃣ CHANGE PASSWORD
===================================================== */
if ($action == "change_password") {

    if (!isset($data['user_id'], $data['old_password'], $data['new_password'])) {
        echo json_encode(["status" => false, "message" => "Missing parameters"]);
        exit();
    }

    $user_id = intval($data['user_id']);
    $old_password = $data['old_password'];
    $new_password = $data['new_password'];

    $stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo json_encode(["status" => false, "message" => "User not found"]);
        exit();
    }

    $row = $result->fetch_assoc();

    if (!password_verify($old_password, $row['password'])) {
        echo json_encode(["status" => false, "message" => "Old password incorrect"]);
        exit();
    }

    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

    $update = $conn->prepare("UPDATE users SET password=? WHERE id=?");
    $update->bind_param("si", $new_hash, $user_id);

    if ($update->execute()) {
        echo json_encode(["status" => true, "message" => "Password changed successfully"]);
    } else {
        echo json_encode(["status" => false, "message" => "Password update failed"]);
    }
}


/* =====================================================
   2️⃣ GET SETTINGS
===================================================== */
elseif ($action == "get_settings") {

    if (!isset($data['user_id'])) {
        echo json_encode(["status" => false, "message" => "User ID required"]);
        exit();
    }

    $user_id = intval($data['user_id']);

    $stmt = $conn->prepare("SELECT notifications_enabled FROM users WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo json_encode(["status" => false, "message" => "User not found"]);
        exit();
    }

    $row = $result->fetch_assoc();

    echo json_encode([
        "status" => true,
        "data" => $row
    ]);
}


/* =====================================================
   3️⃣ UPDATE NOTIFICATIONS
===================================================== */
elseif ($action == "update_notifications") {

    if (!isset($data['user_id'], $data['notifications_enabled'])) {
        echo json_encode(["status" => false, "message" => "Missing parameters"]);
        exit();
    }

    $user_id = intval($data['user_id']);
    $notifications = $data['notifications_enabled'] ? 1 : 0;

    $stmt = $conn->prepare("UPDATE users SET notifications_enabled=? WHERE id=?");
    $stmt->bind_param("ii", $notifications, $user_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => true, "message" => "Notification updated"]);
    } else {
        echo json_encode(["status" => false, "message" => "Update failed"]);
    }
}


/* =====================================================
   4️⃣ LOGOUT
===================================================== */
elseif ($action == "logout") {

    echo json_encode([
        "status" => true,
        "message" => "Logged out successfully"
    ]);
}

else {
    echo json_encode([
        "status" => false,
        "message" => "Invalid action"
    ]);
}

$conn->close();
?>
