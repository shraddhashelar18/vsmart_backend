<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once("../../config.php");

/* =========================
   FETCH DEPARTMENTS
========================= */
$departments = $conn->query("SELECT DISTINCT department FROM classes");

/* =========================
   GET ACTIVE SEMESTER
========================= */
$semData = $conn->query("SELECT active_semester FROM settings WHERE id=1")->fetch_assoc();
$activeSemester = $semData['active_semester']; // EVEN / ODD

/* =========================
   GET SELECTED VALUES
========================= */
$selectedDept = $_GET['department'] ?? '';
$selectedClass = $_GET['class'] ?? '';

$classes = [];
$students = [];

/* =========================
   LOAD CLASSES (FIXED)
========================= */
if($selectedDept){

    $stmt = $conn->prepare("
        SELECT class_name 
        FROM classes 
        WHERE department=?
    ");
    $stmt->bind_param("s", $selectedDept);
    $stmt->execute();

    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()){

        $className = $row['class_name'];

        // extract semester number (3rd character)
        $sem = intval(substr($className, 2, 1));

        if(
            ($activeSemester == 'EVEN' && $sem % 2 == 0) ||
            ($activeSemester == 'ODD' && $sem % 2 == 1)
        ){
            $classes[] = $row;
        }
    }
}

/* =========================
   LOAD STUDENTS + STATUS
========================= */
if($selectedClass){

$stmt = $conn->prepare("
SELECT 
    full_name,
    marks_uploaded as uploaded
FROM students
WHERE `class`=?
");

if(!$stmt){
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param("s", $selectedClass);
$stmt->execute();

$res = $stmt->get_result();

while($row = $res->fetch_assoc()){
    $students[] = $row;
}
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Marksheet Upload Status</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>

body{
margin:0;
font-family:Segoe UI;
background:#f4f6f9;
}

/* HEADER */
.topbar{
background:#009846;
color:white;
padding:18px 30px;
display:flex;
align-items:center;
gap:10px;
}

.back-btn{
color:white;
text-decoration:none;
}

.container{
max-width:700px;
margin:40px auto;
background:white;
padding:25px;
border-radius:12px;
box-shadow:0 6px 18px rgba(0,0,0,0.08);
}

/* SELECT */
.select{
width:100%;
padding:12px;
border-radius:10px;
border:none;
background:#f2f2f2;
margin-bottom:15px;
}

/* CARD */
.card{
background:#fff;
padding:14px;
border-radius:12px;
margin-bottom:12px;
display:flex;
justify-content:space-between;
align-items:center;
box-shadow:0 4px 10px rgba(0,0,0,0.08);
}

.student{
display:flex;
align-items:center;
gap:10px;
}

.status-green{
color:green;
font-weight:600;
}

.status-red{
color:red;
font-weight:600;
}

</style>

</head>

<body>

<div class="topbar">
<a href="result_control.php" class="back-btn">
<span class="material-icons">arrow_back</span>
</a>
<span>Marksheet Upload Status</span>
</div>

<div class="container">

<form method="GET">

<!-- DEPARTMENT -->
<select class="select" name="department" onchange="this.form.submit()">
<option value="">Select Department</option>

<?php while($d = $departments->fetch_assoc()): ?>
<option value="<?=$d['department']?>"
<?=$selectedDept == $d['department'] ? 'selected' : ''?>>
<?=$d['department']?>
</option>
<?php endwhile; ?>

</select>

<!-- CLASS -->
<select class="select" name="class" onchange="this.form.submit()">
<option value="">Select Class</option>

<?php if(!empty($classes)) foreach($classes as $c): ?>
<option value="<?=$c['class_name']?>"
<?=$selectedClass == $c['class_name'] ? 'selected' : ''?>>
<?=$c['class_name']?>
</option>
<?php endforeach; ?>

</select>

</form>

<!-- STUDENTS -->
<?php if(empty($students)): ?>
<p style="color:#777;">No data</p>
<?php else: ?>

<?php foreach($students as $s): ?>

<div class="card">

<div class="student">
<span class="material-icons">person</span>
<?=$s['full_name']?>
</div>

<div class="<?=$s['uploaded'] ? 'status-green' : 'status-red'?>">
<?=$s['uploaded'] ? 'Uploaded' : 'Not Uploaded'?>
</div>

</div>

<?php endforeach; ?>

<?php endif; ?>

</div>

</body>
</html>