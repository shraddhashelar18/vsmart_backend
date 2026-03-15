<?php
require_once("../auth.php");
require_once("../db.php");

if(!isset($_GET['department'])){
die("Department missing");
}

$department=$_GET['department'];

/* GET AVAILABLE TEACHERS */

$teachers=$conn->query("
SELECT user_id,full_name
FROM teachers
WHERE user_id NOT IN (
    SELECT class_teacher
    FROM classes
    WHERE class_teacher IS NOT NULL
)
ORDER BY full_name
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

.wrapper{
max-width:420px;
margin:60px auto;
background:white;
padding:30px;
border-radius:16px;
box-shadow:0 6px 15px rgba(0,0,0,0.08);
}

label{
font-size:14px;
font-weight:600;
}

.input{
width:100%;
padding:14px;
margin-top:8px;
margin-bottom:18px;
border-radius:10px;
border:1px solid #ddd;
background:#f2f2f2;
}

.save{
width:100%;
padding:14px;
background:#009846;
border:none;
border-radius:10px;
color:white;
font-size:16px;
cursor:pointer;
}

.cancel{
width:100%;
padding:14px;
border-radius:10px;
border:1px solid #bbb;
background:white;
margin-top:10px;
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

<select class="input" name="teacher">

<option value="">Select class teacher</option>

<?php while($t=$teachers->fetch_assoc()): ?>

<option value="<?=$t['user_id']?>">
<?=$t['full_name']?>
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