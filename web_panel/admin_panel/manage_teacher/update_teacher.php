<?php

require_once("../../config.php");

$user_id = $_POST['user_id'] ?? '';

if(!$user_id){
die("User ID missing");
}

/* DELETE OLD ASSIGNMENTS */

$conn->query("
DELETE FROM teacher_assignments
WHERE user_id='$user_id'
");

/* INSERT NEW ASSIGNMENTS */

if(isset($_POST['subjects'])){

foreach($_POST['subjects'] as $class=>$subs){

foreach($subs as $sub){

$conn->query("
INSERT INTO teacher_assignments
(user_id,class,subject,status)
VALUES
('$user_id','$class','$sub','active')
");

}

}

}

/* REDIRECT BACK */

header("Location: manage_teachers.php?updated=1");
exit;

?>