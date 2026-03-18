<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../../config.php");

$id = $_GET['id'];

$conn->query("DELETE FROM students WHERE user_id='$id'");
$conn->query("DELETE FROM users WHERE user_id='$id'");

header("Location: ".$_SERVER['HTTP_REFERER']);
exit;
?>