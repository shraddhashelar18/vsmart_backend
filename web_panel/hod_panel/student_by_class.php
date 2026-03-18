<?php
require_once("../config.php");

/* ================= DEPARTMENT ================= */

$department = "IF";   

/* ================= GET ACTIVE SEMESTER ================= */

$setting = $conn->query("SELECT active_semester FROM settings LIMIT 1");
$row = $setting->fetch_assoc();
$active = $row['active_semester'];

/* ================= FETCH CLASSES ================= */

if ($active == "EVEN") {

    $stmt = $conn->prepare("
        SELECT class_name 
        FROM classes
        WHERE department = ?
        AND semester IN (2,4,6)
        ORDER BY semester
    ");

} else {

    $stmt = $conn->prepare("
        SELECT class_name 
        FROM classes
        WHERE department = ?
        AND semester IN (1,3,5)
        ORDER BY semester
    ");

}

$stmt->bind_param("s", $department);
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
color:#000;
padding:18px;
font-size:22px;
font-weight:500;
}

.container{
padding:15px;
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

a{
text-decoration:none;
color:black;
}

</style>

</head>

<body>

<div class="header">
Select Class
</div>

<div class="container">

<?php while ($class = $result->fetch_assoc()) { ?>

<a href="students.php?class=<?php echo $class['class_name']; ?>">

<div class="class-card">

<div>
<?php echo $class['class_name']; ?>
</div>

</div>

</a>

<?php } ?>

</div>

</body>
</html>