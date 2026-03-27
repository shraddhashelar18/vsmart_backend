<?php
session_start();

/* LOGOUT PROCESS */

if(isset($_POST['action']) && $_POST['action']=="logout"){

session_destroy();

/* Redirect to login page */
header("Location: ../auth_panel/login.php");
exit();

}
?>

<!DOCTYPE html>
<html>
<head>
<title>Settings</title>

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
}

.container{
padding:20px;
}

.setting-item{
background:white;
padding:18px;
border-radius:12px;
margin-bottom:15px;
display:flex;
justify-content:space-between;
align-items:center;
box-shadow:0 2px 8px rgba(0,0,0,0.1);
cursor:pointer;
}

.logout-text{
color:#e53935;
}

/* Popup */

.popup{
position:fixed;
top:0;
left:0;
width:100%;
height:100%;
background:rgba(0,0,0,0.4);
display:none;
justify-content:center;
align-items:center;
}

.popup-box{
background:white;
width:320px;
border-radius:14px;
padding:25px;
}

.popup-title{
font-size:22px;
margin-bottom:10px;
}

.popup-msg{
color:#555;
margin-bottom:25px;
}

.popup-buttons{
display:flex;
justify-content:flex-end;
gap:25px;
}

.cancel{
color:#6c63ff;
cursor:pointer;
}

.logout{
color:#e53935;
cursor:pointer;
border:none;
background:none;
font-size:16px;
}

</style>

</head>

<body>

<div class="header">Settings</div>

<div class="container">

<div class="setting-item" onclick="openLogout()">
<span class="logout-text">Logout</span>
<span>➤</span>
</div>

</div>

<!-- Logout Popup -->

<div class="popup" id="logoutPopup">

<div class="popup-box">

<div class="popup-title">Logout</div>

<div class="popup-msg">
Are you sure you want to logout?
</div>

<div class="popup-buttons">

<span class="cancel" onclick="closeLogout()">Cancel</span>

<form method="POST">
<input type="hidden" name="action" value="logout">
<button class="logout" type="submit">Logout</button>
</form>

</div>

</div>

</div>

<script>

function openLogout(){
document.getElementById("logoutPopup").style.display="flex";
}

function closeLogout(){
document.getElementById("logoutPopup").style.display="none";
}

</script>

</body>
</html>