<?php if(isset($_GET['success'])): ?>
<p style="color:green;text-align:center;">
User <?= $_GET['success'] ?> successfully!
</p>
<?php endif; ?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../../config.php");

$result = $conn->query("
SELECT user_id, email, role 
FROM users 
WHERE status='pending'
");

if(!$result){
    die("SQL Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>

<title>User Approvals</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>

body{
margin:0;
font-family:Segoe UI;
background:#f4f6f9;
}

.topbar {
    background: #009846;
    color: white;
    padding: 16px 20px;
    display: flex;
    align-items: center;
}

/* FIX YOUR CURRENT ISSUE */
.back-arrow {
    font-size: 24px;
    margin-right: 12px;
    cursor: pointer;
    text-decoration: none;  /* remove underline */
    color: white;           /* remove blue */
}

/* title */
.title {
    font-size: 20px;
    font-weight: 500;
}

/* CARD */
.card{
background:white;
margin:20px;
padding:15px;
border-radius:12px;
display:flex;
justify-content:space-between;
align-items:center;
box-shadow:0 4px 10px rgba(0,0,0,0.08);
cursor:pointer;
text-decoration:none;
color:black;
}

.user{
display:flex;
align-items:center;
gap:10px;
}

.icon{
background:#e8f5ec;
width:40px;
height:40px;
border-radius:50%;
display:flex;
align-items:center;
justify-content:center;
color:#009846;
}

.role{
color:#777;
font-size:14px;
}

</style>

</head>

<body>

<div class="topbar">
 <a href="../dashboard.php" class="material-icons back-arrow">arrow_back</a>
    <span class="title">User Approvals</span>
</div>

<?php if($result->num_rows > 0): ?>

<?php while($row = $result->fetch_assoc()): ?>

<a class="card" href="verify_user.php?id=<?=$row['user_id']?>">

<div class="user">
<div class="icon">
<span class="material-icons">person</span>
</div>

<div>
<div><?=$row['email']?></div>
<div class="role">Role: <?=strtoupper($row['role'])?></div>
</div>
</div>

<span class="material-icons">chevron_right</span>

</a>

<?php endwhile; ?>

<?php else: ?>

<p style="text-align:center; margin-top:50px; color:#777;">
No Pending Requests
</p>

<?php endif; ?>

</body>
</html>