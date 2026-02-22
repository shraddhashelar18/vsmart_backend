<?php

/* =====================================
   IMPORT REQUIRED FILES
===================================== */

require_once(__DIR__ . "/../config.php");
require_once(__DIR__ . "/../api_guard.php");

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
   SEND FINAL RESPONSE
===================================== */

echo json_encode([
    "status" => true,
    "admin_name" => "Administrator",
    "total_teachers" => $total_teachers,
    "total_students" => $total_students,
    "total_parents"  => $total_parents,
    "total_classes"  => $total_classes
]);

exit;