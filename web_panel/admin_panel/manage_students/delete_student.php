<?php
require_once("../auth.php");
require_once("../db.php");

$id = $_GET['id'];

$conn->query("DELETE FROM students WHERE user_id='$id'");
$conn->query("DELETE FROM users WHERE user_id='$id'");

header("Location: ".$_SERVER['HTTP_REFERER']);
exit;
?>