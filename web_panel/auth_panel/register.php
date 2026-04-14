<?php
require_once("../config.php");

$message = "";
$msgType = "";

if($_SERVER['REQUEST_METHOD']=="POST"){

$fullName = trim($_POST['fullName'] ?? "");
$email = strtolower(trim($_POST['email'] ?? ""));
$password = trim($_POST['password'] ?? "");
$role = trim($_POST['role'] ?? "");

if($fullName=="" || $email=="" || $password=="" || $role==""){
$message = "All fields are required";
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

$check = $conn->prepare("SELECT user_id FROM users WHERE email=?");
$check->bind_param("s",$email);
$check->execute();
$check->store_result();

if($check->num_rows > 0){
$message = "Email already exists";
$msgType = "error";
}
else{

$hashedPassword = password_hash($password,PASSWORD_BCRYPT);
$status = "active";

$stmt = $conn->prepare("INSERT INTO users (email,password,role,status) VALUES (?,?,?,?)");
$stmt->bind_param("ssss",$email,$hashedPassword,$role,$status);
$stmt->execute();

$userId = $conn->insert_id;

/* STUDENT */
if($role=="student"){
$stmt = $conn->prepare("INSERT INTO students (user_id,full_name,roll_no,class,mobile_no,parent_mobile_no,enrollment_no) VALUES (?,?,?,?,?,?,?)");
$stmt->bind_param("issssss",
$userId,
$fullName,
$_POST['rollNo'] ?? "",
$_POST['studentClass'] ?? "",
$_POST['studentMobile'] ?? "",
$_POST['parentMobile'] ?? "",
$_POST['studentEnrollmentNo'] ?? ""
);
$stmt->execute();
}

/* TEACHER */
if($role=="teacher"){
$stmt = $conn->prepare("INSERT INTO teachers (user_id,full_name,employee_id,mobile_no) VALUES (?,?,?,?)");
$stmt->bind_param("isss",
$userId,
$fullName,
$_POST['employeeId'] ?? "",
$_POST['teacherMobile'] ?? ""
);
$stmt->execute();
}

/* PARENT */
if($role=="parent"){
$stmt = $conn->prepare("INSERT INTO parents (user_id,full_name,enrollment_no,mobile_no) VALUES (?,?,?,?)");
$stmt->bind_param("isss",
$userId,
$fullName,
$_POST['enrollmentNo'] ?? "",
$_POST['parentOwnMobile'] ?? ""
);
$stmt->execute();
}

$message = "Registration Successful!";
$msgType = "success";

}
}
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Vsmart Register</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>

body{
font-family:'Segoe UI',sans-serif;
background:#f2f2f2;
margin:0;
display:flex;
justify-content:center;
}

.container{
max-width:360px;
width:100%;
padding:20px;
}

/* ✅ LOGO */
.logo{
margin:30px 0 10px;
text-align:center;
}
.register-logo{
    width:100px;
    height:auto;
    display:block;
    margin:40px auto 10px;
}

.logo img{
width:120px;
height:auto;
display:block;
margin:0 auto;
}

/* TITLE */
.title{
text-align:center;
color:#0a8f3c;
font-size:26px;
font-weight:600;
}

.subtitle{
text-align:center;
color:#999;
font-size:14px;
margin-bottom:25px;
}

/* INPUT */
.input-box{
display:flex;
align-items:center;
background:#e5e5e5;
border-radius:14px;
padding:15px;
margin-bottom:15px;
}

.input-box i{
color:#555;
margin-right:12px;
font-size:22px;
}

input, select{
width:100%;
border:none;
background:transparent;
outline:none;
font-size:16px;
}

/* BUTTON */
button{
width:100%;
background:#0a8f3c;
color:white;
padding:16px;
border:none;
border-radius:14px;
font-size:16px;
margin-top:10px;
}

/* MESSAGE */
.msg{
text-align:center;
margin-top:10px;
font-weight:bold;
}
.success{color:green;}
.error{color:red;}

.back{
text-align:center;
margin-top:15px;
}

.back a{
color:#0a8f3c;
text-decoration:none;
}

.hidden{display:none;}

.right-icon{
cursor:pointer;
color:#555;
}

</style>
</head>

<body>

<div class="container">

<img src="/vsmart/web_panel/assets/logo.png" class="register-logo">

<div class="title">Vsmart</div>
<div class="subtitle">A Smart Academic Management Platform</div>

<form method="POST">

<div class="input-box">
<i class="material-icons">person</i>
<input type="text" name="fullName" placeholder="Full Name" required>
</div>

<div class="input-box">
<i class="material-icons">email</i>
<input type="email" name="email" placeholder="Email Address" required>
</div>

<div class="input-box">
<i class="material-icons">lock</i>
<input type="password" id="password" name="password" placeholder="Password" required>
<i class="material-icons right-icon" onclick="togglePassword()">visibility_off</i>
</div>

<div class="input-box">
<i class="material-icons">group</i>
<select name="role" id="role" onchange="showFields()" required>
<option value="">Select Role</option>
<option value="student">Student</option>
<option value="teacher">Teacher</option>
<option value="parent">Parent</option>
</select>
<i class="material-icons">arrow_drop_down</i>
</div>

<!-- STUDENT -->
<div id="studentFields" class="hidden">

<div class="input-box">
<i class="material-icons">confirmation_number</i>
<input name="studentEnrollmentNo" placeholder="Enrollment No">
</div>

<div class="input-box">
<i class="material-icons">badge</i>
<input name="rollNo" placeholder="Roll No">
</div>

<div class="input-box">
<i class="material-icons">menu_book</i>
<input name="studentClass" placeholder="Class">
</div>

<div class="input-box">
<i class="material-icons">phone</i>
<input name="studentMobile" placeholder="Mobile Number">
</div>

<div class="input-box">
<i class="material-icons">phone</i>
<input name="parentMobile" placeholder="Parent Mobile Number">
</div>

</div>

<button type="submit">Register</button>

</form>

<?php if($message!=""): ?>
<div class="msg <?php echo $msgType; ?>">
<?php echo $message; ?>
</div>
<?php endif; ?>

<div class="back">
<a href="login.php">Back to Login</a>
</div>

</div>

<script>
function togglePassword(){
let pass=document.getElementById("password");
let icon=document.querySelector(".right-icon");

if(pass.type==="password"){
pass.type="text";
icon.innerHTML="visibility";
}else{
pass.type="password";
icon.innerHTML="visibility_off";
}
}

function showFields(){
let role=document.getElementById("role").value;
document.getElementById("studentFields").style.display="none";

if(role=="student"){
document.getElementById("studentFields").style.display="block";
}
}
</script>

</body>
</html>