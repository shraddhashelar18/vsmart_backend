<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../../config.php");

$user_id=$_GET['user_id'];

$user=$conn->query("
SELECT user_id,email,role
FROM users
WHERE user_id='$user_id'
")->fetch_assoc();

$details=[];

if($user['role']=="teacher"){

$details=$conn->query("
SELECT full_name,employee_id,mobile_no
FROM teachers
WHERE user_id='$user_id'
")->fetch_assoc();

}

if($user['role']=="student"){

$details=$conn->query("
SELECT full_name,enrollment_no,roll_no,class,mobile_no,parent_mobile
FROM students
WHERE user_id='$user_id'
")->fetch_assoc();

}

if($user['role']=="parent"){

$details=$conn->query("
SELECT full_name,enrollment_no,mobile_no
FROM parents
WHERE user_id='$user_id'
")->fetch_assoc();

}
?>

<!DOCTYPE html>
<html>
<head>

<title>Verify Registration</title>

<style>

body{
font-family:Segoe UI;
background:#f4f6f9;
margin:0;
}

.topbar{
background:#009846;
color:white;
padding:22px;
font-size:22px;
}

.wrapper{
max-width:700px;
margin:40px auto;
}

.card{
background:white;
padding:30px;
border-radius:18px;
box-shadow:0 6px 15px rgba(0,0,0,0.08);
}

button{
padding:12px 28px;
border:none;
border-radius:10px;
margin-top:20px;
font-size:16px;
cursor:pointer;
}

.approve{background:#009846;color:white;}
.reject{background:red;color:white;}

</style>

</head>

<body>

<div class="topbar">Verify Registration</div>

<div class="wrapper">

<div class="card">

<b>Email:</b> <?=$user['email']?> <br><br>
<b>Role:</b> <?=$user['role']?> <br><br>

<?php foreach($details as $k=>$v): ?>

<b><?=$k?>:</b> <?=$v?><br>

<?php endforeach; ?>

<br>

<a href="approve_user_action.php?user_id=<?=$user_id?>">
<button class="approve">Approve</button>
</a>

<a href="reject_user_action.php?user_id=<?=$user_id?>">
<button class="reject">Reject</button>
</a>

</div>

</div>

</body>
</html>