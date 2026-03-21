<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../config.php");

$message = "";

$id = 1; // your admin user_id

if($_SERVER['REQUEST_METHOD']=="POST"){

    $current = $_POST['current'] ?? '';
    $new = $_POST['new'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    /* FETCH USER */
    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if(!$row){
        die("User not found in DB");
    }

    /* VERIFY PASSWORD */
    if(!password_verify($current, $row['password'])){
        $message = "❌ Wrong current password";
    }
    elseif($new !== $confirm){
        $message = "❌ Passwords do not match";
    }
    else{
        $hashed = password_hash($new, PASSWORD_BCRYPT);

        $update = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
        $update->bind_param("si",$hashed,$id);
        $update->execute();

        if($update->affected_rows > 0){
            $message = "✅ Password updated successfully";
        } else {
            $message = "⚠️ No changes made";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Change Password</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<style>
body{
margin:0;
font-family:Segoe UI;
background:#f4f6f9;
}

.header{
background:#009846;
color:white;
padding:18px;
font-size:20px;
}

.container{
width:420px; /* increased */
margin:50px auto;
background:white;
padding:30px;
border-radius:15px;
box-shadow:0 4px 12px rgba(0,0,0,0.1);
}

/* FIX INPUT OVERFLOW */
input{
width:100%;
padding:14px;
margin-bottom:15px;
border-radius:10px;
border:1px solid #ddd;
box-sizing:border-box; /* 🔥 IMPORTANT FIX */
font-size:14px;
}
input:focus{
outline:none;
border-color:#009846;
box-shadow:0 0 5px rgba(0,152,70,0.3);
}
/* BUTTON */
button{
width:100%;
padding:14px;
background:#009846;
color:white;
border:none;
border-radius:10px;
cursor:pointer;
font-size:15px;
}

/* MESSAGE */
.msg{
margin-bottom:15px;
font-weight:500;
}

.header {
    background: #009846;
    color: white;
    padding: 16px 18px;
    display: flex;
    align-items: center;
}

.back-arrow {
    font-size: 24px;
    margin-right: 12px;
    cursor: pointer;
    text-decoration: none;
    color: white;
}

.title {
    font-size: 20px;
    font-weight: 500;
}
</style>

</head>

<body>
<div class="header">
    <a href="settings.php" class="material-icons back-arrow">arrow_back</a>
    <span class="title">Change Password</span>
</div>

<div class="container">

<?php if($message): ?>
<div class="msg"><?=$message?></div>
<?php endif; ?>

<form method="POST">
<input type="password" name="current" placeholder="Current Password" required>
<input type="password" name="new" placeholder="New Password" required>
<input type="password" name="confirm" placeholder="Confirm Password" required>

<button>Update Password</button>
</form>

</div>
<script>
<script>
function goBack(){
    window.location.href = "settings.php";
}
</script>
</script>
</body>
</html>