<?php
require_once("../../config.php");
require_once("../../api_guard.php");
require_once("../../cors.php");

header("Content-Type: application/json");

if ($currentRole != "admin") {
    echo json_encode([
        "status" => false,
        "message" => "Access denied"
    ]);
    exit;
}

$user_id = $_GET['user_id'] ?? '';

if (empty($user_id) || !is_numeric($user_id)) {
    echo json_encode([
        "status" => false,
        "message" => "Valid user ID required"
    ]);
    exit;
}

/* -----------------------------
GET USER
------------------------------*/

$stmt = $conn->prepare("
SELECT user_id,email,role,status
FROM users
WHERE user_id=?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    echo json_encode([
        "status" => false,
        "message" => "User not found"
    ]);
    exit;
}

$user = $res->fetch_assoc();

/* Only pending users allowed */

if ($user['status'] != "pending") {
    echo json_encode([
        "status" => false,
        "message" => "User already approved"
    ]);
    exit;
}

$details = [];

/* -----------------------------
ROLE BASED DETAILS
------------------------------*/

if ($user['role'] == "teacher") {

    $stmt = $conn->prepare("
        SELECT full_name, employee_id, mobile_no
        FROM teachers
        WHERE user_id=?
    ");

} elseif ($user['role'] == "student") {

    $stmt = $conn->prepare("
        SELECT full_name, enrollment_no, roll_no, class, mobile_no, parent_mobile_no
        FROM students
        WHERE user_id=?
    ");

} elseif ($user['role'] == "parent") {

    $stmt = $conn->prepare("
        SELECT full_name, enrollment_no, mobile_no
        FROM parents
        WHERE user_id=?
    ");

}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$details = $res->fetch_assoc();

/* -----------------------------
FINAL RESPONSE
------------------------------*/

echo json_encode([
    "status" => true,
    "user" => $user,
    "details" => $details
]);