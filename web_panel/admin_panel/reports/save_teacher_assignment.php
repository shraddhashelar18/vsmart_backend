<?php

require_once("../config.php");

$user_id=$_POST['user_id'];
$class=$_POST['class'];
$subject=$_POST['subject'];

$conn->query("
INSERT INTO teacher_assignments
(user_id,class,subject,status)
VALUES
('$user_id','$class','$subject','active')
");

header("Location:user_approvals.php");