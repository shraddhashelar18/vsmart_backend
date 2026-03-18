<?php
require_once("../auth.php");
require_once("../db.php");

$id=$_GET['id'];
$class=$_GET['class'];

$conn->query("
DELETE FROM parents
WHERE id='$id'
");

header("Location: manage_parents.php?class=".$class);
exit;
?>