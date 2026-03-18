<?php
require_once("../config.php");

if(!isset($_GET['class'])){
    echo "Class not selected";
    exit;
}

$class = $_GET['class'];

$stmt = $conn->prepare("
SELECT full_name, roll_no
FROM students
WHERE class = ?
ORDER BY roll_no
");

$stmt->bind_param("s",$class);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>

<head>
<title><?php echo $class; ?> Students</title>

<style>

body{
margin:0;
font-family:Arial;
background:#e9e4ea;
}

/* Header */

.header{
background:#0a8f3c;
padding:18px;
font-size:22px;
display:flex;
align-items:center;
color:white;
}

.back{
margin-right:15px;
font-size:22px;
cursor:pointer;
}

/* Search */

.search-box{
padding:15px;
}

.search-box input{
width:100%;
padding:14px;
border-radius:12px;
border:none;
background:#eee;
font-size:16px;
}

/* Students */

.container{
padding:10px;
}

.student-card{
background:white;
padding:15px;
margin-bottom:14px;
border-radius:12px;
box-shadow:0 2px 6px rgba(0,0,0,0.2);
display:flex;
align-items:center;
justify-content:space-between;
}

.student-info{
display:flex;
align-items:center;
}

.icon{
width:45px;
height:45px;
border-radius:50%;
background:#d8f0df;
display:flex;
align-items:center;
justify-content:center;
font-size:22px;
margin-right:12px;
}

.name{
font-size:18px;
font-weight:500;
}

.roll{
font-size:14px;
color:#666;
}

.arrow{
font-size:22px;
}

.student-link{
text-decoration:none;
color:black;
display:block;
}

</style>

</head>

<body>

<div class="header">
<span class="back" onclick="history.back()">←</span>
<?php echo $class; ?> Students
</div>

<div class="search-box">
<input type="text" placeholder="Search student...">
</div>

<div class="container">

<?php while($student = $result->fetch_assoc()){ ?>

<a href="student_details.php?roll=<?php echo $student['roll_no']; ?>" class="student-link">

<div class="student-card">

<div class="student-info">

<div class="icon">👤</div>

<div>
<div class="name"><?php echo $student['full_name']; ?></div>
<div class="roll">Roll No: <?php echo $student['roll_no']; ?></div>
</div>

</div>

<div class="arrow">›</div>

</div>

</a>

<?php } ?>

</div>

</body>
</html>