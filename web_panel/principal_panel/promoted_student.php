<?php
require_once("../config.php");
require_once("../promotion_helper.php");

if(!isset($_GET['class'])){
    echo "Class required";
    exit;
}

$class = $_GET['class'];

/* ================= ATKT LIMIT ================= */

$setting = $conn->query("SELECT atkt_limit FROM settings LIMIT 1");
$atktLimit = (int)$setting->fetch_assoc()['atkt_limit'];

/* ================= GET STUDENTS ================= */

$stmt = $conn->prepare("
SELECT user_id, full_name, class, current_semester, status
FROM students
WHERE class = ?
");

$stmt->bind_param("s",$class);
$stmt->execute();
$result = $stmt->get_result();

$students = [];

while($row = $result->fetch_assoc()){

    $studentId = $row['user_id'];

    $promotion = calculatePromotion($conn,$studentId,$atktLimit);

    /* show only promoted */

    if(
        $promotion['status'] != "PROMOTED" &&
        strtolower($row['status']) != "passed_out"
    ){
        continue;
    }

    $currentClass = $row['class'];
    $currentSemester = (int)$row['current_semester'];

    $department = substr($currentClass,0,2);
    $division = substr($currentClass,-2);

    $oldSemester = $currentSemester - 1;
    $oldClass = $department.$oldSemester.$division;

    $newSemester = $currentSemester;
    $newClass = $currentClass;

    if($promotion['status']=="PROMOTED" && $currentSemester < 6){
        $newSemester = $currentSemester + 1;
        $newClass = $department.$newSemester.$division;
    }

    $displayStatus = ($row['status']=="passed_out")
        ? "PASSED_OUT"
        : $promotion['status'];

    /* ✅ ADD PERCENTAGE HERE */
    $percentage = $promotion['percentage'] ?? "N/A";

    $students[] = [
        "name"=>$row['full_name'],
        "oldClass"=>$oldClass,
        "newClass"=>$newClass,
        "oldSemester"=>$oldSemester,
        "newSemester"=>$newSemester,
        "status"=>$displayStatus,
        "percentage"=>$percentage
    ];
}
?>

<!DOCTYPE html>
<html>

<head>

<title><?php echo $class ?> Promoted Students</title>

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
display:flex;
align-items:center;
}

.back{
margin-right:15px;
cursor:pointer;
}

.container{
padding:15px;
}

.student-card{
background:white;
padding:15px;
margin-bottom:15px;
border-radius:12px;
box-shadow:0 2px 6px rgba(0,0,0,0.2);
}

.name{
font-size:18px;
font-weight:500;
}

.info{
color:#666;
font-size:14px;
margin-top:4px;
}

.status{
margin-top:6px;
font-weight:bold;
color:#0a8f3c;
}

/* NEW STYLE */
.percentage{
margin-top:6px;
font-weight:bold;
color:#2c3e50;
}

</style>

</head>

<body>

<div class="header">
<span class="back" onclick="history.back()">←</span>
<?php echo $class ?> Promoted
</div>

<div class="container">

<?php foreach($students as $s){ ?>

<div class="student-card">

<div class="name">
<?php echo $s['name']; ?>
</div>

<div class="info">
Class: <?php echo $s['oldClass']; ?> → <?php echo $s['newClass']; ?>
</div>

<div class="info">
Semester: <?php echo $s['oldSemester']; ?> → <?php echo $s['newSemester']; ?>
</div>

<!-- ✅ NEW: FINAL PERCENTAGE -->
<div class="percentage">
Final Percentage: <?php echo $s['percentage']; ?>%
</div>

<div class="status">
<?php echo $s['status']; ?>
</div>

</div>

<?php } ?>

</div>

</body>
</html>