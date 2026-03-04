<?php
require_once "auth.php";
require_once "db.php";

$id=intval($_GET['id']);

$conn->begin_transaction();

try{
$conn->query("DELETE FROM teacher_assignments WHERE user_id=$id");
$conn->query("DELETE FROM teachers WHERE user_id=$id");
$conn->query("DELETE FROM users WHERE user_id=$id");
$conn->commit();
}catch(Exception $e){
$conn->rollback();
}

header("Location: manage_teachers.php");
exit;
