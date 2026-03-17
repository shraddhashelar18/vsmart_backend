<?php
require_once("../config.php");

$query = "SELECT DISTINCT department 
          FROM classes
          ORDER BY department";

$result = mysqli_query($conn,$query);
?>

<!DOCTYPE html>
<html>
<head>
<title>Principal Dashboard</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<style>

body{
font-family:Arial;
background:#f3f3f3;
margin:0;
}

.header{
background:#009846;
color:white;
padding:20px;
font-size:22px;
}

.container{
padding:20px;
}

.title{
font-size:20px;
font-weight:bold;
margin-bottom:20px;
}

.card{
background:white;
padding:18px;
border-radius:14px;
margin-bottom:15px;
display:flex;
justify-content:space-between;
align-items:center;
box-shadow:0 4px 10px rgba(0,0,0,0.08);
text-decoration:none;
color:black;
}

.left{
display:flex;
align-items:center;
gap:12px;
}

.icon{
background:#e6f5ec;
padding:10px;
border-radius:8px;
color:#009846;
font-size:18px;
}
.dept-icon{
font-size:28px;
color:#0a8f3c;
vertical-align:middle;
margin-right:10px;
}

.arrow{
color:#888;
}

.bottom{
position:fixed;
bottom:0;
width:100%;
background:white;
display:flex;
justify-content:space-around;
padding:10px;
border-top:1px solid #ddd;
}

.active{
color:#009846;
}

</style>
</head>

<body>

<div class="header">
Principal Dashboard
</div>

<div class="container">

<div class="title">Select Department</div>

<?php while($row = mysqli_fetch_assoc($result)){ ?>

<a class="card" href="department_summary.php?department=<?php echo $row['department']; ?>">

<div class="left">
<span class="material-icons dept-icon">apartment</span>
<div><?php echo $row['department']; ?> Department</div>
</div>

<div class="arrow">➤</div>

</a>

<?php } ?>

</div>

<div class="bottom">
<div class="active">🏠<br>Dashboard</div>
<div>⚙️<br>Settings</div>
</div>

</body>
</html>