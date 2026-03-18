<?php
require_once("../config.php");

if(!isset($_GET['department'])){
die("Department missing");
}

$department=$_GET['department'];

/* GET AVAILABLE TEACHERS */

$teachers=$conn->query("
SELECT t.user_id,t.full_name,c.class_name
FROM teachers t
LEFT JOIN classes c
ON c.class_teacher=t.user_id
ORDER BY t.full_name
");
?>

<!DOCTYPE html>
<html>
<head>

<title>Add Class</title>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>

body{
margin:0;
font-family:Segoe UI;
background:#f4f6f9;
}

/* TOPBAR */

.topbar{
background:#009846;
color:white;
padding:20px 30px;
font-size:22px;
display:flex;
align-items:center;
gap:12px;
}

/* BACK BUTTON */

.back{
color:white;
text-decoration:none;
font-size:26px;
}

/* CENTER FORM */

.wrapper{
max-width:650px;
margin:80px auto;
background:white;
padding:45px;
border-radius:18px;
box-shadow:0 8px 18px rgba(0,0,0,0.08);
}

/* LABEL */

label{
font-size:15px;
font-weight:600;
}

/* INPUT */

.input{
width:100%;
padding:18px;
margin-top:10px;
margin-bottom:24px;
border-radius:10px;
border:1px solid #ddd;
background:#f2f2f2;
font-size:15px;
}

/* SAVE BUTTON */

.save{
width:100%;
padding:16px;
background:#009846;
border:none;
border-radius:12px;
color:white;
font-size:17px;
cursor:pointer;
margin-top:5px;
}

/* CANCEL BUTTON */

.cancel{
width:100%;
padding:16px;
border-radius:12px;
border:1px solid #bbb;
background:white;
margin-top:12px;
font-size:15px;
cursor:pointer;
}
</style>
</head>

<body>

<div class="topbar">

<a href="manage_classes.php?department=<?=$department?>" class="back">
<span class="material-icons">arrow_back</span>
</a>

Add Class

</div>

<div class="wrapper">

<form method="POST" action="save_class.php">

<label>Class Name</label>

<input
class="input"
name="class_name"
placeholder="Enter class name (e.g. IF6KA)"
required>

<label>Department</label>

<input
class="input"
value="<?=$department?>"
readonly>

<input type="hidden" name="department" value="<?=$department?>">

<label>Class Teacher</label>

<select class="input" name="class_teacher">

<option value="">Select class teacher</option>

<?php while($t=$teachers->fetch_assoc()): ?>

<option
value="<?=$t['user_id']?>"
<?= $t['class_name'] ? 'disabled' : '' ?>
>

<?=$t['full_name']?> 
<?= $t['class_name'] ? "(Assigned to ".$t['class_name'].")" : "" ?>

</option>

<?php endwhile; ?>

</select>
<button class="save">Save Class</button>

<button
type="button"
class="cancel"
onclick="location.href='manage_classes.php?department=<?=$department?>'">
Cancel
</button>

</form>

</div>

</body>
</html>