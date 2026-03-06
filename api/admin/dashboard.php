<?php

/* =====================================
   IMPORT REQUIRED FILES
===================================== */

require_once(__DIR__ . "/../config.php");
require_once(__DIR__ . "/../api_guard.php");
require_once("../cors.php");
/* =====================================
   SET RESPONSE TYPE
===================================== */

header("Content-Type: application/json");

/* =====================================
   INITIALIZE VARIABLES
===================================== */

$total_teachers = 0;
$total_students = 0;
$total_parents  = 0;
$total_classes  = 0;

/* =====================================
   GET TOTAL TEACHERS
===================================== */

$result = $conn->query("SELECT COUNT(*) AS total FROM teachers");
if ($result) {
    $row = $result->fetch_assoc();
    $total_teachers = intval($row['total']);
}

/* =====================================
   GET TOTAL STUDENTS
===================================== */

$result = $conn->query("SELECT COUNT(*) AS total FROM students");
if ($result) {
    $row = $result->fetch_assoc();
    $total_students = intval($row['total']);
}

/* =====================================
   GET TOTAL PARENTS
===================================== */

$result = $conn->query("SELECT COUNT(*) AS total FROM parents");
if ($result) {
    $row = $result->fetch_assoc();
    $total_parents = intval($row['total']);
}

/* =====================================
   GET TOTAL CLASSES
   (Now includes semester column)
===================================== */

$result = $conn->query("SELECT COUNT(*) AS total FROM classes");
if ($result) {
    $row = $result->fetch_assoc();
    $total_classes = intval($row['total']);
}

/* =====================================
   GET ADMIN NAME
===================================== */
//
$admin_name = "";

$stmt = $conn->prepare("SELECT full_name FROM admins WHERE user_id = ?");
$stmt->bind_param("i", $currentUserId);   // user id coming from api_guard
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $admin_name = $row['full_name'];
}
/* =====================================
   SEND FINAL RESPONSE
===================================== */
echo json_encode([
    "status" => true,
    "admin_name" => $admin_name,
    "total_teachers" => $total_teachers,
    "total_students" => $total_students,
    "total_parents"  => $total_parents,
    "total_classes"  => $total_classes
]);

exit;