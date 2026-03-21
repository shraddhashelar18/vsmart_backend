<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../../config.php");

$id = $_GET['id'] ?? 0;

/* GET USER */
$stmt = $conn->prepare("
SELECT user_id, email, role 
FROM users 
WHERE user_id=?
");
$stmt->bind_param("i",$id);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

if(!$user){
    die("User not found");
}

$details = [];

/* ROLE BASED FETCH */

if($user['role']=="student"){

$q=$conn->prepare("
SELECT full_name, enrollment_no, roll_no, class, mobile_no, parent_mobile_no 
FROM students 
WHERE user_id=?
");
$q->bind_param("i",$id);
$q->execute();
$details=$q->get_result()->fetch_assoc() ?? [];

}

elseif($user['role']=="teacher"){

$q=$conn->prepare("
SELECT full_name, employee_id, mobile_no 
FROM teachers 
WHERE user_id=?
");
$q->bind_param("i",$id);
$q->execute();
$details=$q->get_result()->fetch_assoc() ?? [];

}

elseif($user['role']=="parent"){

$q=$conn->prepare("
SELECT full_name, enrollment_no, mobile_no 
FROM parents 
WHERE user_id=?
");
$q->bind_param("i",$id);
$q->execute();
$details=$q->get_result()->fetch_assoc() ?? [];

}
?>

<!DOCTYPE html>
<html>
<head>

<title>Verify Registration</title>

<style>

body{
margin:0;
font-family:Segoe UI;
background:#f4f6f9;
}

.topbar{
background:#009846;
color:white;
padding:18px;
font-size:18px;
}

.card{
background:white;
margin:20px;
padding:20px;
border-radius:12px;
box-shadow:0 4px 10px rgba(0,0,0,0.08);
}

.label{
color:#777;
font-size:14px;
}

.value{
font-size:16px;
margin-bottom:12px;
}

/* BUTTONS */

.actions{
display:flex;
gap:15px;
margin:20px;
}

.btn{
flex:1;
padding:15px;
border:none;
border-radius:10px;
color:white;
font-size:16px;
cursor:pointer;
}

.reject{ background:red; }
.approve{ background:#009846; }

</style>

</head>

<body>

<div class="topbar">
Verify Registration
</div>

<div class="card">

<h3><?=$details['full_name'] ?? ''?></h3>
<p>Role: <?=strtoupper($user['role'])?></p>

<hr>

<div class="label">Email</div>
<div class="value"><?=$user['email']?></div>

<?php if($user['role']=="student"): ?>

<div class="label">Enrollment No</div>
<div class="value"><?=$details['enrollment_no'] ?? ''?></div>

<div class="label">Roll No</div>
<div class="value"><?=$details['roll_no'] ?? ''?></div>

<div class="label">Class</div>
<div class="value"><?=$details['class'] ?? ''?></div>

<div class="label">Mobile</div>
<div class="value"><?=$details['mobile_no'] ?? ''?></div>

<div class="label">Parent Mobile</div>
<div class="value"><?=$details['parent_mobile_no'] ?? ''?></div>

<?php endif; ?>

<?php if($user['role']=="teacher"): ?>

<div class="label">Employee ID</div>
<div class="value"><?=$details['employee_id'] ?? ''?></div>

<div class="label">Mobile</div>
<div class="value"><?=$details['mobile_no'] ?? ''?></div>

<?php endif; ?>

<?php if($user['role']=="parent"): ?>

<div class="label">Enrollment No</div>
<div class="value"><?=$details['enrollment_no'] ?? ''?></div>

<div class="label">Mobile</div>
<div class="value"><?=$details['mobile_no'] ?? ''?></div>

<?php endif; ?>

</div>

<div class="actions">

<form method="POST" action="reject_user.php">
<input type="hidden" name="id" value="<?=$id?>">
<button class="btn reject">Reject</button>
</form>

<form method="POST" action="approve_user.php">
<input type="hidden" name="id" value="<?=$id?>">
<button class="btn approve">Approve</button>
</form>

</div>

</body>
</html>