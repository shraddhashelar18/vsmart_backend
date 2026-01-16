<?php
include("../db.php");
session_start();

/* 🔐 ADMIN ONLY */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

/* GET USER ID SAFELY */
$user_id = (int)$_GET['user_id'];

/* APPROVE USER */
mysqli_query(
    $conn,
    "UPDATE users SET status='approved' WHERE user_id=$user_id"
);

/* BACK TO APPROVALS */
header("Location: approvals.php");
exit;
