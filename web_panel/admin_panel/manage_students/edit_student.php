<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../../config.php");

$id = $_GET['id'];

$data = $conn->query("
SELECT s.*,u.email
FROM students s
JOIN users u ON u.user_id=s.user_id
WHERE s.user_id='$id'
")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>

<title>Edit Student</title>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>

body{
margin:0;
background:#f5f7f9;
font-family:Segoe UI;
}

.readonly{
background:#e0e0e0 !important;
color:#888;
}

/* TOP BAR */

.topbar{
background:#009846;
color:white;
padding:20px;
font-size:22px;
display:flex;
align-items:center;
gap:10px;
}

.back{
color:white;
text-decoration:none;
font-size:26px;
}

/* CENTER FORM */

.container{
display:flex;
justify-content:center;
margin-top:70px;
}

.form-box{
background:white;
padding:40px;
border-radius:22px;
width:520px;
box-shadow:0 10px 25px rgba(0,0,0,0.1);
}

.input-group{
display:flex;
align-items:center;
border:1px solid #ccc;
border-radius:16px;
padding:16px;
margin-bottom:18px;
background:#f8f8f8;
}

.input-group span{
margin-right:12px;
color:#666;
font-size:22px;
}

.input-group input{
border:none;
outline:none;
background:transparent;
width:100%;
font-size:17px;
}

.btn{
width:100%;
background:#009846;
color:white;
border:none;
padding:16px;
font-size:18px;
border-radius:35px;
cursor:pointer;
}

.msg{
margin-bottom:15px;
font-weight:500;
}

</style>

</head>

<body>

<div class="topbar">
<a href="manage_students.php?class=<?=$data['class']?>" class="back">
<span class="material-icons">arrow_back</span>
</a>
Edit Student
</div>

<div class="container">

<div class="form-box">

<div id="msg" class="msg"></div>

<form id="editForm">

<input type="hidden" id="user_id" value="<?=$data['user_id']?>">

<div class="input-group">
<span class="material-icons">person</span>
<input id="name" value="<?=$data['full_name']?>" required>
</div>

<div class="input-group readonly">
<span class="material-icons">mail</span>
<input value="<?=$data['email']?>" readonly>
</div>

<div class="input-group">
<span class="material-icons">call</span>
<input id="phone" value="<?=$data['mobile_no']?>" maxlength="10">
</div>

<div class="input-group">
<span class="material-icons">call</span>
<input id="parentPhone" value="<?=$data['parent_mobile_no']?>" maxlength="10">
</div>

<div class="input-group">
<span class="material-icons">badge</span>
<input id="roll" value="<?=$data['roll_no']?>">
</div>


<div class="input-group readonly">
<span class="material-icons">tag</span>
<input id="enrollment" value="<?=$data['enrollment_no']?>" readonly>
</div>


<div class="input-group readonly">
<span class="material-icons">school</span>
<input value="<?=$data['class']?>" readonly>
</div>

<button class="btn" type="submit">Update Student</button>

</form>

</div>
</div>

<script>

document.getElementById("editForm").addEventListener("submit",async function(e){

e.preventDefault();

let data = {
user_id: document.getElementById("user_id").value,
name: document.getElementById("name").value,
phone: document.getElementById("phone").value,
parentPhone: document.getElementById("parentPhone").value,
roll: document.getElementById("roll").value,
enrollment: document.getElementById("enrollment").value
};

let res = await fetch("../../api/admin/edit_student.php",{
method:"POST",
headers:{
"Content-Type":"application/json"
},
body: JSON.stringify(data)
});

let result = await res.json();

let msg = document.getElementById("msg");

if(result.status){

msg.style.color="green";
msg.innerHTML=result.message;

setTimeout(()=>{
window.location.href="manage_students.php?class=<?=$data['class']?>";
},1500);

}else{

msg.style.color="red";
msg.innerHTML=result.message;

}

});

</script>

</body>
</html>