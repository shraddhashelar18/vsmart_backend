<?php
require_once("../auth.php");
require_once("../db.php");

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