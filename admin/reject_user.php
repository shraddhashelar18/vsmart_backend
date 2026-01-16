<?php
include("../db.php");
session_start();

if ($_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'];

mysqli_query(
    $conn,
    "UPDATE users SET status='rejected' WHERE user_id=$id"
);

header("Location: approvals.php");