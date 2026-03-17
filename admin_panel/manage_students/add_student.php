<?php
require_once("../auth.php");
require_once("../db.php");

$class = $_GET['class'];
$error="";

if(isset($_POST['save'])){

$name = trim($_POST['name']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$phone = $_POST['phone'];
$parentPhone = $_POST['parentPhone'];
$roll = $_POST['roll'];
$enrollment = $_POST['enrollment'];

if(
empty($name) ||
empty($email) ||
empty($password) ||
empty($phone) ||
empty($parentPhone) ||
empty($roll) ||
empty($enrollment)
){
$error="All fields required";
}

elseif(!preg_match("/^[A-Za-z0-9]{10}$/",$roll)){
$error="Roll must be exactly 10 alphanumeric characters";
}

elseif(!preg_match("/^[0-9]{10}$/",$phone)){
$error="Mobile number must be 10 digits";
}

elseif(!preg_match("/^[0-9]{10}$/",$parentPhone)){
$error="Parent mobile must be 10 digits";
}

elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)){
$error="Invalid email";
}

else{

$conn->begin_transaction();

try{

$checkEmail=$conn->prepare("SELECT user_id FROM users WHERE email=?");
$checkEmail->bind_param("s",$email);
$checkEmail->execute();
$checkEmail->store_result();

if($checkEmail->num_rows>0){
throw new Exception("Email already exists");
}

$checkEnroll=$conn->prepare("SELECT enrollment_no FROM students WHERE enrollment_no=?");
$checkEnroll->bind_param("s",$enrollment);
$checkEnroll->execute();
$checkEnroll->store_result();

if($checkEnroll->num_rows>0){
throw new Exception("Enrollment already exists");
}

$hashedPassword=password_hash($password,PASSWORD_DEFAULT);

$stmtUser=$conn->prepare("
INSERT INTO users(email,password,role,status)
VALUES(?,?,'student','approved')
");

$stmtUser->bind_param("ss",$email,$hashedPassword);
$stmtUser->execute();

$user_id=$conn->insert_id;

$department=substr($class,0,2);

preg_match('/\d+/',$class,$match);
$semester="SEM".($match[0] ?? "1");

$stmtStudent=$conn->prepare("
INSERT INTO students
(roll_no,user_id,full_name,class,
mobile_no,parent_mobile_no,
enrollment_no,department,
current_semester,status)
VALUES(?,?,?,?,?,?,?,?,?,'studying')
");

$stmtStudent->bind_param(
"sisssssss",
$roll,
$user_id,
$name,
$class,
$phone,
$parentPhone,
$enrollment,
$department,
$semester
);

$stmtStudent->execute();

$stmtParent=$conn->prepare("
UPDATE parents
SET enrollment_no=?
WHERE mobile_no=?
");

$stmtParent->bind_param("ss",$enrollment,$parentPhone);
$stmtParent->execute();

$conn->commit();

header("Location: manage_students.php?class=".$class);
exit;

}catch(Exception $e){

$conn->rollback();
$error=$e->getMessage();

}

}

}
?>

<!DOCTYPE html>
<html>
<head>

<title>Add Student</title>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>

/* GLOBAL */

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:Arial,sans-serif;
}

body{
background:#f5f7f9;
}

/* TOPBAR */

.topbar{
background:#009846;
color:white;
padding:18px 25px;
font-size:20px;
display:flex;
align-items:center;
gap:12px;
}

.back{
color:white;
text-decoration:none;
font-size:24px;
}

/* FORM WRAPPER */

.form-wrapper{
display:flex;
justify-content:center;
margin-top:60px;
}

/* FORM CARD */

.student-form{
width:380px;
background:white;
padding:25px;
border-radius:18px;
box-shadow:0 6px 15px rgba(0,0,0,0.1);
}

/* INPUT GROUP */

.input-group{
display:flex;
align-items:center;
border:1px solid #ddd;
border-radius:14px;
padding:12px 14px;
margin-bottom:14px;
background:#f7f7f7;
}

.input-group span{
margin-right:10px;
color:#777;
}

.input-group input{
border:none;
outline:none;
width:100%;
background:transparent;
font-size:15px;
}

/* BUTTON */

.submit-btn{
width:100%;
background:#009846;
color:white;
border:none;
padding:14px;
border-radius:12px;
font-size:16px;
cursor:pointer;
}

.submit-btn:hover{
background:#007a38;
}

.error{
color:red;
margin-bottom:10px;
}

</style>

</head>

<body>

<div class="topbar">
<a href="manage_students.php?class=<?=$class?>" class="back">
<span class="material-icons">arrow_back</span>
</a>
Add Student
</div>

<div class="form-wrapper">

<form method="POST" class="student-form">

<?php if($error!="") echo "<p class='error'>$error</p>"; ?>

<div class="input-group">
<span class="material-icons">person</span>
<input name="name" placeholder="Full Name" required>
</div>

<div class="input-group">
<span class="material-icons">mail</span>
<input name="email" type="email" placeholder="Email" required>
</div>

<div class="input-group">
<span class="material-icons">lock</span>
<input name="password" type="password" placeholder="Password" required>
</div>

<div class="input-group">
<span class="material-icons">call</span>
<input name="phone" placeholder="Mobile Number" maxlength="10" required>
</div>

<div class="input-group">
<span class="material-icons">call</span>
<input name="parentPhone" placeholder="Parent Mobile Number" maxlength="10" required>
</div>

<div class="input-group">
<span class="material-icons">badge</span>
<input name="roll" placeholder="Roll Number" maxlength="10" required>
</div>

<div class="input-group">
<span class="material-icons">tag</span>
<input name="enrollment" placeholder="Enrollment Number" maxlength="11" required>
</div>

<div class="input-group">
<span class="material-icons">school</span>
<input value="<?=$class?>" readonly>
</div>

<button class="submit-btn" name="save">Add Student</button>

</form>

</div>

</body>
</html>