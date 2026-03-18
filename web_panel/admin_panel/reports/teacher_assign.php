<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../../config.php");

$user_id=$_GET['user_id'];

$classes=$conn->query("SELECT class_name FROM classes");

?>

<!DOCTYPE html>
<html>
<head>

<title>Assign Teacher</title>

<style>

body{
font-family:Segoe UI;
background:#f4f6f9;
margin:0;
}

.topbar{
background:#009846;
color:white;
padding:22px;
font-size:22px;
}

.wrapper{
max-width:600px;
margin:40px auto;
}

.card{
background:white;
padding:30px;
border-radius:18px;
box-shadow:0 6px 15px rgba(0,0,0,0.08);
}

button{
margin-top:20px;
padding:12px;
width:100%;
background:#009846;
border:none;
border-radius:10px;
color:white;
font-size:16px;
}

</style>

</head>

<body>

<div class="topbar">Assign Teacher</div>

<div class="wrapper">

<div class="card">

<form method="POST" action="save_teacher_assignment.php">

<input type="hidden" name="user_id" value="<?=$user_id?>">

<select name="class">

<?php while($c=$classes->fetch_assoc()): ?>

<option><?=$c['class_name']?></option>

<?php endwhile; ?>

</select>

<br><br>

<input name="subject" placeholder="Subject">

<button>Assign</button>

</form>

</div>

</div>

</body>
</html>
