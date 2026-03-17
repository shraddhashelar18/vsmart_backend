<?php
require_once("../auth.php");
require_once("../db.php");

$result=$conn->query("
SELECT 
u.user_id,
u.email,
u.role,
COALESCE(s.full_name,t.full_name) AS name
FROM users u
LEFT JOIN students s ON u.user_id=s.user_id
LEFT JOIN teachers t ON u.user_id=t.user_id
WHERE u.status='pending'
");
?>

<!DOCTYPE html>
<html>
<head>

<title>User Approvals</title>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>

body{
margin:0;
font-family:Segoe UI;
background:#f4f6f9;
}

.topbar{
background:#009846;
color:white;
padding:22px;
font-size:22px;
}

.wrapper{
max-width:800px;
margin:40px auto;
}

.card{
background:white;
border-radius:16px;
padding:20px;
margin-bottom:15px;
box-shadow:0 6px 15px rgba(0,0,0,0.08);
display:flex;
justify-content:space-between;
}

</style>

</head>

<body>

<div class="topbar">User Approvals</div>

<div class="wrapper">

<?php while($row=$result->fetch_assoc()): ?>

<div class="card">

<div>

<b><?=$row['name']?></b><br>
<?=$row['email']?><br>
Role: <?=$row['role']?>

</div>

<div>

<a href="verify_registration.php?user_id=<?=$row['user_id']?>">

<span class="material-icons">chevron_right</span>

</a>

</div>

</div>

<?php endwhile; ?>

</div>

</body>
</html>