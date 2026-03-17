<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<title>Vsmart Register</title>

<style>

body{
font-family:Arial;
background:#f3f3f3;
margin:0;
padding:0;
}

.container{
width:360px;
margin:auto;
padding-top:40px;
}

.logo{
width:80px;
height:80px;
background:#0a8f3c;
border-radius:50%;
margin:auto;
display:flex;
align-items:center;
justify-content:center;
}

.logo i{
color:white;
font-size:32px;
}

.title{
text-align:center;
color:#0a8f3c;
font-size:26px;
margin-top:10px;
}

.subtitle{
text-align:center;
color:#777;
font-size:14px;
margin-bottom:20px;
}

.input-box{
background:#e9e9e9;
border-radius:12px;
padding:12px;
margin-top:12px;
}

.input-box input,
.input-box select{
width:100%;
border:none;
background:transparent;
outline:none;
font-size:15px;
}

button{
width:100%;
background:#0a8f3c;
border:none;
color:white;
padding:14px;
border-radius:12px;
margin-top:20px;
font-size:16px;
}

.back{
text-align:center;
margin-top:15px;
color:#0a8f3c;
}

.hidden{
display:none;
}

</style>

</head>

<body>

<div class="container">

<div class="logo">
<i class="fa-solid fa-graduation-cap"></i>
</div>

<div class="title">Vsmart</div>
<div class="subtitle">A Smart Academic Management Platform</div>

<div class="input-box">
<input type="text" id="fullName" placeholder="Full Name">
</div>

<div class="input-box">
<input type="email" id="email" placeholder="Email Address">
</div>

<div class="input-box">
<input type="password" id="password" placeholder="Password">
</div>

<div class="input-box">
<select id="role" onchange="showFields()">
<option value="">Select Role</option>
<option value="student">Student</option>
<option value="teacher">Teacher</option>
<option value="parent">Parent</option>
</select>
</div>

<!-- STUDENT FIELDS -->

<div id="studentFields" class="hidden">

<div class="input-box">
<input type="text" id="studentEnrollmentNo" placeholder="Enrollment No">
</div>

<div class="input-box">
<input type="text" id="rollNo" placeholder="Roll No">
</div>

<div class="input-box">
<input type="text" id="studentClass" placeholder="Class">
</div>

<div class="input-box">
<input type="text" id="studentMobile" placeholder="Mobile Number">
</div>

<div class="input-box">
<input type="text" id="parentMobile" placeholder="Parent Mobile Number">
</div>

</div>

<!-- TEACHER FIELDS -->

<div id="teacherFields" class="hidden">

<div class="input-box">
<input type="text" id="employeeId" placeholder="Employee ID">
</div>

<div class="input-box">
<input type="text" id="teacherMobile" placeholder="Mobile Number">
</div>

</div>

<!-- PARENT FIELDS -->

<div id="parentFields" class="hidden">

<div class="input-box">
<input type="text" id="enrollmentNo" placeholder="Enrollment No">
</div>

<div class="input-box">
<input type="text" id="parentOwnMobile" placeholder="Mobile Number">
</div>

</div>

<button onclick="register()">Register</button>

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

function register(){

let data={
fullName:document.getElementById("fullName").value,
email:document.getElementById("email").value,
password:document.getElementById("password").value,
selectedRole:document.getElementById("role").value
};

if(data.selectedRole=="student"){

data.studentEnrollmentNo=document.getElementById("studentEnrollmentNo").value;
data.rollNo=document.getElementById("rollNo").value;
data.studentClass=document.getElementById("studentClass").value;
data.studentMobile=document.getElementById("studentMobile").value;
data.parentMobile=document.getElementById("parentMobile").value;

}

if(data.selectedRole=="teacher"){

data.employeeId=document.getElementById("employeeId").value;
data.teacherMobile=document.getElementById("teacherMobile").value;

}

if(data.selectedRole=="parent"){

data.enrollmentNo=document.getElementById("enrollmentNo").value;
data.parentOwnMobile=document.getElementById("parentOwnMobile").value;

}

fetch("http://localhost/vsmart_backend/api/auth/register.php",{

method:"POST",

headers:{
"Content-Type":"application/json"
},

body:JSON.stringify(data)

})

.then(res=>res.json())
.then(response=>{

alert(response.message);

})
.catch(err=>{
alert("Server error");
});

}

</script>

</body>
</html>