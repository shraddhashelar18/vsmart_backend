<?php
require_once("../config.php");

if(!isset($_GET['class'])){
    echo "Class not selected";
    exit;
}

$class = $_GET['class'];

/* GET DETAINED STUDENTS DIRECTLY */
$stmt = $conn->prepare("
SELECT user_id, full_name 
FROM students 
WHERE class = ?
AND status = 'detained'
");

$stmt->bind_param("s",$class);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
<title>Detained Students</title>

<style>
body{margin:0;font-family:Arial;background:#e9e4ea;}
.header{background:#0a8f3c;color:white;padding:18px;font-size:22px;}
.container{padding:15px;}
.student-card{
background:white;padding:18px;margin-bottom:15px;
border-radius:12px;box-shadow:0 2px 6px rgba(0,0,0,0.2);
}
.name{font-size:18px;font-weight:bold;}
.empty{
text-align:center;font-size:16px;color:#555;
background:white;padding:20px;border-radius:12px;
}
</style>

</head>
<body>

<div class="header">
Detained Students - <?php echo $class; ?>
</div>

<div class="container">

<?php
if($result->num_rows > 0){

    while($row = $result->fetch_assoc()){
?>

<div class="student-card">
<div class="name">
<?php echo $row['full_name']; ?>
</div>
<div>Status : DETAINED</div>
</div>

<?php
    }

}else{
    echo "<div class='empty'>No detained students found</div>";
}
?>

</div>

</body>
</html>