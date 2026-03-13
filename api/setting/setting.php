<?php
require_once("../config.php");
require_once("../api_guard.php");
error_reporting(0);
ini_set('display_errors', 0);
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['action'])) {
    echo json_encode(["status" => false, "message" => "Action required"]);
    exit;
}

$action = $data['action'];
$role = $currentRole;        // from api_guard
$userId = $currentUserId;    // from api_guard

/* =====================================================
   1️⃣ SETTINGS (ALL ROLES)
===================================================== */
if ($action == "settings") {

    $result = $conn->query("SELECT * FROM settings WHERE id=1");
    $row = $result->fetch_assoc();

    echo json_encode([
        "status" => true,
        "activeSemester" => $row['active_semester'],
        "registrationOpen" => (bool) $row['registration_open'],
        "attendanceLocked" => $row['attendance_locked'] == 1,
        "atktLimit" => (int) $row['atkt_limit'],
        "ct1Published" => isset($row['ct1_published']) ? (bool) $row['ct1_published'] : false,
        "ct2Published" => isset($row['ct2_published']) ? (bool) $row['ct2_published'] : false,
        "finalPublished" => (bool) $row['final_published']
    ]);
    exit;
}


/* =====================================================
   2️⃣ UPDATE SETTINGS (ADMIN ONLY)
===================================================== */
if ($action == "update_academic") {

    if ($role != "admin") {
        echo json_encode(["status" => false, "message" => "Access Denied"]);
        exit;
    }

    $activeSemester = $data['activeSemester'];
    $registrationOpen = (int) $data['registrationOpen'];
    $attendanceLocked = (int) $data['attendanceLocked'];
    $atktLimit = (int) $data['atktLimit'];

    // lock attendance logic
    $stmt = $conn->prepare("
UPDATE settings
SET active_semester=?,
    registration_open=?,
    attendance_locked=?,
    atkt_limit=?
WHERE id=1
");

    $stmt->bind_param(
        "siii",
        $activeSemester,
        $registrationOpen,
        $attendanceLocked,
        $atktLimit
    );
    if ($stmt->execute()) {
        echo json_encode(["status" => true, "message" => "Settings Updated"]);
    } else {
        echo json_encode(["status" => false, "message" => "Update Failed"]);
    }
    exit;
}


/* =====================================================
   3️⃣ CHANGE PASSWORD (ALL ROLES)
===================================================== */
/* =====================================================
   3️⃣ CHANGE PASSWORD (ALL ROLES)
===================================================== */

if ($action == "change_password") {

    if (!isset($data['currentPassword']) || !isset($data['newPassword'])) {
        echo json_encode(["status" => false, "message" => "Password fields required"]);
        exit;
    }

    $currentPassword = $data['currentPassword'];
    $newPasswordRaw = $data['newPassword'];

    // Get stored password hash
    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id=?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo json_encode(["status" => false, "message" => "User not found"]);
        exit;
    }

    $row = $result->fetch_assoc();
    $storedHash = $row['password'];

    // Verify current password
    if (!password_verify($currentPassword, $storedHash)) {
        echo json_encode(["status" => false, "message" => "Current password incorrect"]);
        exit;
    }

    // Hash new password
    $newPassword = password_hash($newPasswordRaw, PASSWORD_DEFAULT);

    // Update password
    $stmt = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
    $stmt->bind_param("si", $newPassword, $userId);

    if ($stmt->execute()) {
        echo json_encode(["status" => true, "message" => "Password changed successfully"]);
    } else {
        echo json_encode(["status" => false, "message" => "Password update failed"]);
    }

    exit;
}
/* =====================================================
   4️⃣ LOGOUT
===================================================== */
if ($action == "logout") {

    session_destroy();

    echo json_encode([
        "status" => true,
        "message" => "Logged out successfully"
    ]);
    exit;
}

echo json_encode(["status" => false, "message" => "Invalid Action"]);