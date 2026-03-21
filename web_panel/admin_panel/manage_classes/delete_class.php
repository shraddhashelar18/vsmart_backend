<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../../config.php");

if(!isset($_GET['id'])){
die("Class ID missing");
}

$id=$_GET['id'];
$department=$_GET['department'];

/* DELETE CLASS */

$conn->query("
DELETE FROM classes
WHERE class_id='$id'
");

/* REDIRECT BACK */

header("Location: manage_classes.php?department=".$department);
exit;
?>