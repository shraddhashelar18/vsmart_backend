<?php
require_once("../../config.php");
require_once("../../api_guard.php");
require_once("../../cors.php");
error_reporting(E_ALL);
ini_set('display_errors', 0);
header("Content-Type: application/json");
if($currentRole != "admin"){
    echo json_encode(["status"=>false,"message"=>"Access denied"]);
    exit;
}

if (!isset($_GET['user_id'])) {
    echo json_encode([
        "status" => false,
        "message" => "user_id missing"
    ]);
    exit;
}

$user_id = $_GET['user_id'];

$userQuery = "SELECT user_id,email,role FROM users WHERE user_id='$user_id'";
$userResult = $conn->query($userQuery);

if (!$userResult) {
    echo json_encode([
        "status" => false,
        "message" => "User query failed"
    ]);
    exit;
}

$user = $userResult->fetch_assoc();

$details = [];

if ($user['role'] == 'student') {

    $sql = "SELECT enrollment_no, roll_no, class, mobile_no, parent_mobile
            FROM students
            WHERE user_id='$user_id'";

    $res = $conn->query($sql);

    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();

        $details = [
            "Enrollment No" => $row['enrollment_no'] ?? "",
            "Roll No" => $row['roll_no'] ?? "",
            "Class" => $row['class'] ?? "",
            "Mobile" => $row['mobile_no'] ?? "",
            "Parent Mobile" => $row['parent_mobile'] ?? ""
        ];
    } else {
        $details = [
            "Enrollment No" => "Not provided",
            "Roll No" => "Not provided",
            "Class" => "Not provided",
            "Mobile" => "Not provided",
            "Parent Mobile" => "Not provided"
        ];
    }
}
if ($user['role'] == 'teacher') {

    $sql = "SELECT employee_id, mobile_no 
            FROM teachers 
            WHERE user_id='$user_id'";

    $res = $conn->query($sql);

    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();

        $details = [
            "Employee ID" => $row['employee_id'] ?? "",
            "Mobile" => $row['mobile_no'] ?? ""
        ];
    } else {
        $details = [
            "Employee ID" => "Not provided",
            "Mobile" => "Not provided"
        ];
    }
}

if ($user['role'] == 'parent') {

    $sql = "SELECT enrollment_no, mobile_no
            FROM parents
            WHERE user_id='$user_id'";

    $res = $conn->query($sql);

    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();

        $details = [
            "Enrollment No" => $row['enrollment_no'] ?? "",
            "Mobile" => $row['mobile_no'] ?? ""
        ];
    } else {
        $details = [
            "Enrollment No" => "Not provided",
            "Mobile" => "Not provided"
        ];
    }
}

echo json_encode([
    "status" => true,
    "user" => $user,
    "details" => $details
]);