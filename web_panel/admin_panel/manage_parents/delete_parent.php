<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../../config.php");

$id=$_GET['id'];
$class=$_GET['class'];

$conn->query("
DELETE FROM parents
WHERE id='$id'
");

header("Location: manage_parents.php?class=".$class);
exit;
?>