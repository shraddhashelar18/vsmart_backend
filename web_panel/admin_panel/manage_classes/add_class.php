<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../../config.php");

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

.back{
color:white;
text-decoration:none;
font-size:26px;
}

/* WRAPPER */
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
color:#444;
}

/* FIELD WITH ICON */
.field{
display:flex;
align-items:center;
gap:12px;
background:#f2f2f2;
padding:18px;
border-radius:12px;
margin-top:10px;
margin-bottom:25px;
border:1px solid #ddd;
}

.field .material-icons{
color:#777;
font-size:22px;
}

.field input{
border:none;
background:transparent;
outline:none;
font-size:16px;
width:100%;
}

/* DISABLED */
.field.disabled{
background:#e0e0e0;
}

/* SELECT */
.select-box{
position:relative;
}

.select-box select{
width:100%;
padding:18px;
border-radius:12px;
border:1px solid #ddd;
background:#f2f2f2;
font-size:16px;
appearance:none;
}

/* DROPDOWN ICON */
.arrow{
position:absolute;
right:15px;
top:50%;
transform:translateY(-50%);
pointer-events:none;
color:#555;
font-size:22px;
}

/* NOTE */
.note{
background:#e8f5ec;
color:#2e7d32;
padding:15px;
border-radius:12px;
margin-bottom:20px;
display:flex;
align-items:center;
gap:12px;
font-size:14px;
}

.note .material-icons{
color:#2e7d32;
}

/* BUTTON */
.save{
width:100%;
padding:16px;
background:#009846;
border:none;
border-radius:12px;
color:white;
font-size:17px;
cursor:pointer;
}

/* CANCEL */
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

<!-- CLASS NAME -->
<label>Class Name</label>
<div class="field">
<span class="material-icons">bookmark</span>
<input
name="class_name"
placeholder="Enter class name (e.g. IF6KA)"
required>
</div>

<!-- DEPARTMENT -->
<label>Department</label>
<div class="field disabled">
<span class="material-icons">school</span>
<input
value="<?=$department?>"
readonly>
</div>

<input type="hidden" name="department" value="<?=$department?>">

<!-- CLASS TEACHER -->
<label>Class Teacher</label>

<div class="select-box">

<select name="class_teacher">

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

<span class="material-icons arrow">expand_more</span>

</div>

<!-- NOTE -->
<div class="note">
<span class="material-icons">info</span>
<span>Students and teachers can be assigned later.</span>
</div>

<!-- BUTTONS -->
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