<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../config.php");
session_start();

/* ================= LOGIN CHECK ================= */
if(!isset($_SESSION['user_id'])){
    die("Login required");
}

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];

/* ================= ROLE BASED DEPARTMENT ================= */

if($role == "hod"){

    // HOD → get their department
    $hodQuery = $conn->prepare("
    SELECT department 
    FROM hods 
    WHERE user_id = ?
    ");

    $hodQuery->bind_param("i",$userId);
    $hodQuery->execute();
    $hodResult = $hodQuery->get_result();

    if($hodResult->num_rows == 0){
        die("HOD not found");
    }

    $department = $hodResult->fetch_assoc()['department'];

}else if($role == "principal"){

    // Principal → get department from URL
    if(isset($_GET['department'])){
        $department = $_GET['department'];
    }else{
        die("Department not selected");
    }

}else{
    die("Access denied");
}


/* ================= GET ACTIVE SEMESTER ================= */

$setting = $conn->query("
SELECT active_semester
FROM settings
LIMIT 1
");

$active = strtoupper(trim($setting->fetch_assoc()['active_semester']));


/* ================= FETCH CLASSES ================= */

if($active == "EVEN"){

    // EVEN → SHOW ODD
    $stmt = $conn->prepare("
    SELECT class_name
    FROM classes
    WHERE department = ?
    AND semester IN (1,3,5)
    ORDER BY semester,class_name
    ");

}else{

    // ODD → SHOW EVEN
    $stmt = $conn->prepare("
    SELECT class_name
    FROM classes
    WHERE department = ?
    AND semester IN (2,4,6)
    ORDER BY semester,class_name
    ");

}

$stmt->bind_param("s",$department);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>

<head>

<title>Select Class</title>

<style>

body{
margin:0;
font-family:Arial;
background:#e9e4ea;
}

.header{
background:#0a8f3c;
color:white;
padding:18px;
font-size:22px;
}

.container{
padding:15px;
}

.class-link{
text-decoration:none;
color:black;
}

.class-card{
background:white;
padding:18px;
margin-bottom:15px;
border-radius:12px;
box-shadow:0 2px 6px rgba(0,0,0,0.2);
display:flex;
justify-content:space-between;
align-items:center;
font-size:18px;
}

.arrow{
font-size:20px;
}

</style>

</head>

<body>

<div class="header">
Detained Students 
</div>

<div class="container">

<?php 
if($result->num_rows == 0){
    echo "<p>No classes found</p>";
}

while($row = $result->fetch_assoc()){ 
?>

<a href="detained_student.php?class=<?php echo $row['class_name']; ?>&department=<?php echo $department; ?>" class="class-link">

<div class="class-card">

<div><?php echo $row['class_name']; ?></div>

<div class="arrow">›</div>

</div>

</a>

<?php } ?>

</div>

</body>
</html>