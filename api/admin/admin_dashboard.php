<?php
require_once "../config.php";
require_once "../api_guard.php";

header("Content-Type: application/json");

/* ---------- TOTAL TEACHERS ---------- */
$teachers = $conn->query(
    "SELECT COUNT(*) AS total 
     FROM users 
     WHERE role='teacher' AND status='approved'"
)->fetch_assoc()['total'];

/* ---------- TOTAL STUDENTS ---------- */
$students = $conn->query(
    "SELECT COUNT(*) AS total 
     FROM users 
     WHERE role='student' AND status='approved'"
)->fetch_assoc()['total'];

/* ---------- TOTAL PARENTS ---------- */
$parents = $conn->query(
    "SELECT COUNT(*) AS total 
     FROM users 
     WHERE role='parent' AND status='approved'"
)->fetch_assoc()['total'];

/* ---------- TOTAL CLASSES ---------- */
$classes = $conn->query(
    "SELECT COUNT(*) AS total 
     FROM classes"
)->fetch_assoc()['total'];

/* ---------- RESPONSE ---------- */
echo json_encode([
    "status" => true,
    "message" => "Dashboard data fetched successfully",
    "data" => [
        "admin_name" => "Administrator",
        "total_teachers" => (int)$teachers,
        "total_students" => (int)$students,
        "total_parents" => (int)$parents,
        "total_classes" => (int)$classes
    ]
]);
