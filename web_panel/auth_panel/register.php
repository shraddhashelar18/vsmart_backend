<?php
require_once("../config.php");

$message = "";
$msgType = "";

/* 🔥 DEBUG: confirm correct file */
# echo "RUNNING THIS FILE"; exit;

if($_SERVER['REQUEST_METHOD']=="POST"){

$fullName = trim($_POST['fullName'] ?? "");
$email = strtolower(trim($_POST['email'] ?? ""));
$password = trim($_POST['password'] ?? "");
$role = trim($_POST['role'] ?? "");

/* VALIDATION */

if($fullName=="" || $email=="" || $password=="" || $role==""){
$message = "All fields required";
$msgType = "error";
}
elseif(preg_match('/[0-9]/',$fullName)){
$message = "Name cannot contain numbers";
$msgType = "error";
}
elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)){
$message = "Invalid email";
$msgType = "error";
}
elseif(strlen($password) < 6){
$message = "Password must be at least 6 characters";
$msgType = "error";
}
else{

/* CHECK REGISTRATION */

$res = $conn->query("SELECT registration_open FROM settings WHERE id=1");

if(!$res){
die("Settings error: ".$conn->error);
}

$row = $res->fetch_assoc();

if($row && $row['registration_open']==0){
$message = "Registration closed by admin";
$msgType = "error";
}
else{

/* CHECK EMAIL */

$check = $conn->prepare("SELECT user_id FROM users WHERE email=?");

if(!$check){
die("Prepare error: ".$conn->error);
}

$check->bind_param("s",$email);
$check->execute();
$check->store_result();

if($check->num_rows > 0){
$message = "Email already exists";
$msgType = "error";
}
else{

/* INSERT USER (NO full_name) */

$hashedPassword = password_hash($password,PASSWORD_BCRYPT);
$status = "pending";

$stmt = $conn->prepare("
INSERT INTO users (email,password,role,status)
VALUES (?,?,?,?)
");

if(!$stmt){
die("User prepare error: ".$conn->error);
}

$stmt->bind_param("ssss",$email,$hashedPassword,$role,$status);

if(!$stmt->execute()){
die("User insert error: ".$stmt->error);
}

$userId = $conn->insert_id;

/* ================= STUDENT ================= */

if($role=="student"){

$stmt = $conn->prepare("
INSERT INTO students
(user_id,full_name,roll_no,class,mobile_no,parent_mobile_no,enrollment_no)
VALUES (?,?,?,?,?,?,?)
");

if(!$stmt){
die("Student prepare error: ".$conn->error);
}

$stmt->bind_param("issssss",
$userId,
$fullName,
$_POST['rollNo'],
$_POST['studentClass'],
$_POST['studentMobile'],
$_POST['parentMobile'],
$_POST['studentEnrollmentNo']
);

if(!$stmt->execute()){
die("Student insert error: ".$stmt->error);
}
}

/* ================= TEACHER ================= */

if($role=="teacher"){

$stmt = $conn->prepare("
INSERT INTO teachers
(user_id,full_name,employee_id,mobile_no)
VALUES (?,?,?,?)
");

if(!$stmt){
die("Teacher prepare error: ".$conn->error);
}

$stmt->bind_param("isss",
$userId,
$fullName,
$_POST['employeeId'],
$_POST['teacherMobile']
);

if(!$stmt->execute()){
die("Teacher insert error: ".$stmt->error);
}
}

/* ================= PARENT ================= */

if($role=="parent"){

$stmt = $conn->prepare("
INSERT INTO parents
(user_id,full_name,enrollment_no,mobile_no)
VALUES (?,?,?,?)
");

if(!$stmt){
die("Parent prepare error: ".$conn->error);
}

$stmt->bind_param("isss",
$userId,
$fullName,
$_POST['enrollmentNo'],
$_POST['parentOwnMobile']
);

if(!$stmt->execute()){
die("Parent insert error: ".$stmt->error);
}
}

$message = "✅ Registration Successful!";
$msgType = "success";

}
}
}
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Vsmart Register</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>

body{
font-family: 'Segoe UI', sans-serif;
background: linear-gradient(135deg,#e6f4ea,#f3f3f3);
margin:0;
}

/* CONTAINER */
.container{
width:360px;
margin:auto;
margin-top:40px;
padding:25px;
background:white;
border-radius:20px;
box-shadow:0 10px 30px rgba(0,0,0,0.1);
}

/* LOGO */
.logo{
width:90px;
height:90px;
background:#0a8f3c;
border-radius:50%;
margin:auto;
display:flex;
align-items:center;
justify-content:center;
color:white;
font-size:34px;
box-shadow:0 5px 15px rgba(0,0,0,0.2);
}

/* TITLE */
.title{
text-align:center;
color:#0a8f3c;
font-size:28px;
margin-top:15px;
font-weight:bold;
}

.subtitle{
text-align:center;
color:#777;
font-size:14px;
margin-bottom:20px;
}

/* INPUT BOX */
.input-box{
background:#f1f1f1;
border-radius:12px;
padding:14px;
margin-top:12px;
transition:0.3s;
}

.input-box:hover{
background:#e7f5ec;
}

input,select{
width:100%;
border:none;
background:transparent;
outline:none;
font-size:15px;
}

/* BUTTON */
button{
width:100%;
background:#0a8f3c;
color:white;
padding:14px;
border:none;
border-radius:12px;
margin-top:20px;
font-size:16px;
cursor:pointer;
transition:0.3s;
}

button:hover{
background:#087531;
}

/* MESSAGE */
.msg{
text-align:center;
margin-top:10px;
font-weight:bold;
}

.success{ color:green; }
.error{ color:red; }

/* BACK LINK */
.back{
text-align:center;
margin-top:15px;
}

.back a{
color:#0a8f3c;
text-decoration:none;
font-weight:bold;
}

/* HIDDEN */
.hidden{
display:none;
}

</style>

</head>

<body>

<div class="container">

<div class="logo">🎓</div>

<div class="title">Vsmart</div>
<div class="subtitle">A Smart Academic Management Platform</div>

<form method="POST">

<div class="input-box">
<input type="text" name="fullName" placeholder="Full Name" required>
</div>

<div class="input-box">
<input type="email" name="email" placeholder="Email Address" required>
</div>

<div class="input-box">
<input type="password" name="password" placeholder="Password" required>
</div>

<div class="input-box">
<select name="role" id="role" onchange="showFields()" required>
<option value="">Select Role</option>
<option value="student">Student</option>
<option value="teacher">Teacher</option>
<option value="parent">Parent</option>
</select>
</div>

<!-- STUDENT -->
<div id="studentFields" class="hidden">

<div class="input-box">
<input name="studentEnrollmentNo" placeholder="Enrollment No">
</div>

<div class="input-box">
<input name="rollNo" placeholder="Roll No">
</div>

<div class="input-box">
<input name="studentClass" placeholder="Class">
</div>

<div class="input-box">
<input name="studentMobile" placeholder="Mobile Number">
</div>

<div class="input-box">
<input name="parentMobile" placeholder="Parent Mobile">
</div>

</div>

<!-- TEACHER -->
<div id="teacherFields" class="hidden">

<div class="input-box">
<input name="employeeId" placeholder="Employee ID">
</div>

<div class="input-box">
<input name="teacherMobile" placeholder="Mobile Number">
</div>

</div>

<!-- PARENT -->
<div id="parentFields" class="hidden">

<div class="input-box">
<input name="enrollmentNo" placeholder="Enrollment No">
</div>

<div class="input-box">
<input name="parentOwnMobile" placeholder="Mobile Number">
</div>

</div>

<button type="submit">Register</button>

</form>

<div class="back">
<a href="login.php">Back to Login</a>
</div>

</div>

<script>
function showFields(){
let role=document.getElementById("role").value;

document.getElementById("studentFields").style.display="none";
document.getElementById("teacherFields").style.display="none";
document.getElementById("parentFields").style.display="none";

if(role=="student"){
document.getElementById("studentFields").style.display="block";
}

if(role=="teacher"){
document.getElementById("teacherFields").style.display="block";
}

if(role=="parent"){
document.getElementById("parentFields").style.display="block";
}
}
</script>

</body>
</html>