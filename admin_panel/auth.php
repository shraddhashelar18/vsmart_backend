<?php
session_start();
require_once "db.php";

if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit;
}

$stmt=$conn->prepare("SELECT role FROM users WHERE user_id=?");
$stmt->bind_param("i",$_SESSION['admin_id']);
$stmt->execute();
$res=$stmt->get_result();

if($res->num_rows==0){
    session_destroy();
    header("Location: login.php");
    exit;
}

$user=$res->fetch_assoc();
if($user['role']!='admin'){
    session_destroy();
    header("Location: login.php");
    exit;
}
?>