<?php
session_start();
require_once("../config.php");

if(!isset($_SESSION['teacher_id'])){
    header("Location: ../auth_panel/login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

/* FETCH DEPARTMENTS */
$result = $conn->query("
SELECT DISTINCT department 
FROM teacher_assignments 
WHERE user_id = '$teacher_id'
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Select Department</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>

body{
margin:0;
font-family:Arial;
background:#f3f3f3;
}

/* HEADER */
.header{
background:#009846;
color:white;
padding:20px;
font-size:22px;
border-bottom-left-radius:30px;
border-bottom-right-radius:30px;
display:flex;
align-items:center;
}

.back-btn{
font-size:26px;
margin-right:10px;
cursor:pointer;
}
/* CONTAINER */
.container{
padding:30px 20px;
}

/* INPUT GROUP */
.input-group{
margin-top:40px;
}

.input-group label{
font-size:14px;
color:#555;
display:block;
margin-bottom:5px;
}

/* DROPDOWN STYLE */
.select-box{
width:100%;
border:none;
border-bottom:2px solid #999;
padding:10px 5px;
font-size:18px;
background:transparent;
outline:none;
}

/* BUTTON */
.btn{
margin-top:40px;
width:60%;
padding:12px;
border:none;
border-radius:25px;
background:#ddd;
font-size:16px;
display:block;
margin-left:auto;
margin-right:auto;
cursor:pointer;
transition:0.3s;
}

.btn.active{
background:#009846;
color:white;
}

</style>

</head>

<body>

<div class="header">
    <span class="material-icons back-btn" onclick="location.href='teacher_dashboard.php'">
        arrow_back
    </span>
    <span>Select Department</span>
</div>

<div class="container">

<form method="post">

<div class="input-group">
<label>Department</label>

<select name="department" class="select-box" required onchange="enableBtn()">
<option value="">Select Department</option>

<?php while($row = $result->fetch_assoc()){ ?>
<option value="<?= $row['department'] ?>">
<?= $row['department'] ?>
</option>
<?php } ?>

</select>
</div>

<button id="btn" class="btn" name="submit">Continue</button>

</form>

</div>

<script>
function enableBtn(){
document.getElementById("btn").classList.add("active");
}
</script>

</body>
</html>

<?php
if(isset($_POST['submit'])){
    $_SESSION['department_id'] = $_POST['department'];
    header("Location: teacher_dashboard.php");
}
?>