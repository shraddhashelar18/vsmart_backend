<?php require_once "auth.php"; ?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Select Department</title>
<style>
body{margin:0;font-family:Arial;background:#f3f3f3;}
.header{
    background:#0a8f3c;
    color:white;
    padding:20px;
    border-bottom-left-radius:25px;
    border-bottom-right-radius:25px;
    font-size:20px;
    display:flex;
    align-items:center;
}
.back{
    margin-right:15px;
    color:white;
    text-decoration:none;
    font-size:22px;
}
.container{padding:20px;}
.card{
    background:white;
    padding:20px;
    border-radius:15px;
    margin-bottom:20px;
    box-shadow:0 4px 10px rgba(0,0,0,0.1);
    display:flex;
    justify-content:space-between;
    text-decoration:none;
    color:black;
    font-size:18px;
}
</style>
</head>
<body>

<div class="header">
<a href="dashboard.php" class="back">←</a>
Select Department
</div>

<div class="container">
<a href="manage_teachers.php?dept=IF" class="card">IF Department →</a>
<a href="manage_teachers.php?dept=CO" class="card">CO Department →</a>
<a href="manage_teachers.php?dept=EJ" class="card">EJ Department →</a>
</div>

</body>
</html>