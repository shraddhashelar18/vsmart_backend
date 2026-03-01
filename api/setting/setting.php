<?php
require_once("../config.php");
require_once("../api_guard.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['action'])) {
    echo json_encode(["status"=>false,"message"=>"Action required"]);
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
      "registrationOpen" => (bool)$row['registration_open'],
        "attendanceLocked" => $row['allow_reupload'] == 0 ? true : false,
        "atktLimit" => (int)$row['atkt_limit'],
        "ct1Published" => (bool)$row['ct1_published'],
        "ct2Published" => (bool)$row['ct2_published'],
        "finalPublished" => (bool)$row['final_published']
    ]);
    exit;
}


/* =====================================================
   2️⃣ UPDATE SETTINGS (ADMIN ONLY)
===================================================== */
if ($action == "update_academic") {

    if ($role != "admin") {
        echo json_encode(["status"=>false,"message"=>"Access Denied"]);
        exit;
    }

    $activeSemester = $data['activeSemester'];
    $registrationOpen = $data['registrationOpen'];
    $attendanceLocked = $data['attendanceLocked'];
    $atktLimit = $data['atktLimit'];

    // lock attendance logic
    $allowReupload = $attendanceLocked ? 0 : 1;

    $stmt = $conn->prepare("
        UPDATE settings
        SET active_semester=?,
            allow_marksheet_upload=?,
            allow_reupload=?,
            atkt_limit=?
        WHERE id=1
    ");

    $stmt->bind_param(
        "siii",
        $activeSemester,
        $registrationOpen,
        $allowReupload,
        $atktLimit
    );

    if ($stmt->execute()) {
        echo json_encode(["status"=>true,"message"=>"Settings Updated"]);
    } else {
        echo json_encode(["status"=>false,"message"=>"Update Failed"]);
    }
    exit;
}


/* =====================================================
   3️⃣ CHANGE PASSWORD (ALL ROLES)
===================================================== */
if ($action == "change_password") {

    $currentPassword = $data['currentPassword'];
    $newPassword     = password_hash($data['newPassword'], PASSWORD_DEFAULT);

    switch ($role) {
        case "admin":     $table = "admins"; break;
        case "principal": $table = "principals"; break;
        case "hod":       $table = "hods"; break;
        case "teacher":   $table = "teachers"; break;
        case "parent":    $table = "parents"; break;
        case "student":   $table = "students"; break;
        default:
            echo json_encode(["status"=>false,"message"=>"Invalid Role"]);
            exit;
    }

    $result = $conn->query("SELECT password FROM $table WHERE user_id='$userId'");
    $row = $result->fetch_assoc();

    if (!password_verify($currentPassword, $row['password'])) {
        echo json_encode(["status"=>false,"message"=>"Current password incorrect"]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE $table SET password=? WHERE user_id=?");
    $stmt->bind_param("ss", $newPassword, $userId);

    if ($stmt->execute()) {
        echo json_encode(["status"=>true,"message"=>"Password changed"]);
    } else {
        echo json_encode(["status"=>false,"message"=>"Failed"]);
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

echo json_encode(["status"=>false,"message"=>"Invalid Action"]);