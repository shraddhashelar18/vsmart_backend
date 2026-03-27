<?php
require_once("../config.php");
session_start();

if(!isset($_SESSION['user_id'])){
    echo "Login required";
    exit;
}

$userId = $_SESSION['user_id'];

if(isset($_POST['update'])){

$current = $_POST['current'];
$new = $_POST['new'];
$confirm = $_POST['confirm'];

$q = $conn->query("SELECT password FROM users WHERE user_id='$userId'");
$row = $q->fetch_assoc();

if(!password_verify($current,$row['password'])){
$msg="Current password wrong";
}

else if($new != $confirm){
$msg="Passwords do not match";
}

else{

$hash = password_hash($new,PASSWORD_BCRYPT);

$conn->query("UPDATE users SET password='$hash' WHERE user_id='$userId'");

$msg="Password updated successfully";
}

}
?>

<!DOCTYPE html>
<html>
<head>

<title>Change Password</title>

<style>

body{
margin:0;
font-family:Arial;
background:#f2f2f2;
}

.header{
background:#0a8f3c;
color:white;
padding:18px;
font-size:22px;
display:flex;
align-items:center;
}

.back{
margin-right:10px;
cursor:pointer;
}

.container{
padding:25px;
}

.input-box{
background:white;
border-radius:8px;
padding:12px;
margin-bottom:20px;
box-shadow:0 2px 5px rgba(0,0,0,0.1);
}

.input-box input{
width:100%;
border:none;
outline:none;
font-size:16px;
}

button{
width:100%;
padding:14px;
background:#0a8f3c;
color:white;
border:none;
border-radius:30px;
font-size:16px;
cursor:pointer;
}

.message{
margin-top:15px;
text-align:center;
color:red;
}

</style>

</head>

<body>

<div class="header">

<div class="back" onclick="history.back()">←</div>

Change Password

</div>

<div class="container">

<form method="POST"> 

<div class="input-box">
<input type="password" name="current" placeholder="Current Password" required>
</div>

<div class="input-box">
<input type="password" name="new" placeholder="New Password" required>
</div>

<div class="input-box">
<input type="password" name="confirm" placeholder="Confirm Password" required>
</div>

<button name="update">Update Password</button>

</form>

<?php if(isset($msg)){ ?>
<div class="message"><?php echo $msg; ?></div>
<?php } ?>

</div>

</body>
</html>