<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once("../config.php");

/* =========================
   🔒 LOGIN CHECK
========================= */
if(!isset($_SESSION['user_id'])){
    echo "<script>
        alert('Please login first');
        window.location.href='../../auth_panel/login.php';
    </script>";
    exit();
}

$role = $_SESSION['role'] ?? '';
$userId = $_SESSION['user_id'];

/* =========================
   🎯 GET DEPARTMENT
========================= */
$department = "";

/* HOD */
if($role == "hod"){

    $deptQuery = $conn->prepare("SELECT department FROM hods WHERE user_id=?");

    if(!$deptQuery){
        die("Dept Query Error: " . $conn->error);
    }

    $deptQuery->bind_param("i",$userId);
    $deptQuery->execute();

    $res = $deptQuery->get_result();

    if($res && $res->num_rows > 0){
        $department = $res->fetch_assoc()['department'];
    }else{
        die("❌ No department found for HOD");
    }
}

/* PRINCIPAL */
elseif($role == "principal"){
    $department = $_GET['department'] ?? "";
}

/* INVALID ROLE */
else{
    die("❌ Access denied");
}

/* =========================
   ⚙️ SETTINGS
========================= */
$activeSemester = "odd";

$set = $conn->query("SELECT active_semester FROM settings LIMIT 1");

if($set && $set->num_rows > 0){
    $activeSemester = strtolower(trim($set->fetch_assoc()['active_semester']));
}

/* =========================
   👨‍🎓 STUDENT STATS
========================= */

$totalStudents = 0;
$promoted = 0;
$atkt = 0;
$detained = 0;
$totalTeachers = 0;

if(!empty($department)){

    $isOdd = ($activeSemester === 'odd');

    if($isOdd){

        $query = "
        SELECT status FROM students 
        WHERE department=? 
        AND CAST(SUBSTRING(class, LENGTH(?) + 1, 1) AS UNSIGNED) % 2 = 1
        ";

    }else{

        $query = "
        SELECT status FROM students 
        WHERE department=? 
        AND CAST(SUBSTRING(class, LENGTH(?) + 1, 1) AS UNSIGNED) % 2 = 0
        ";
    }

    $stmt = $conn->prepare($query);

    if(!$stmt){
        die("Student Query Error: " . $conn->error);
    }

    $stmt->bind_param("ss", $department, $department);
    $stmt->execute();

    $res = $stmt->get_result();

    if(!$res){
        die("Student Result Error: " . $conn->error);
    }

    while($row = $res->fetch_assoc()){

        $totalStudents++;

        $status = strtolower(trim($row['status']));

        if($status == "passed_out" || $status == "promoted"){
            $promoted++;
        }
        elseif($status == "promoted_with_atkt"){
            $atkt++;
        }
        elseif($status == "detained"){
            $detained++;
        }
    }

    /* =========================
       👨‍🏫 TEACHERS COUNT
    ========================= */
    $t = $conn->prepare("
    SELECT COUNT(DISTINCT ta.user_id) AS totalTeachers
    FROM teacher_assignments ta
    WHERE ta.department = ?
    ");

    if(!$t){
        die("Teacher Query Error: " . $conn->error);
    }

    $t->bind_param("s", $department);
    $t->execute();

    $resultT = $t->get_result();

    if(!$resultT){
        die("Teacher Result Error: " . $conn->error);
    }

    $totalTeachers = $resultT->fetch_assoc()['totalTeachers'] ?? 0;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard</title>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>
body{margin:0;font-family:'Segoe UI',sans-serif;background:#eef1f5;}
.header{background:#0a8f3c;color:white;padding:20px 30px;}
.header h2{margin:0;font-size:24px;}
.container{padding:25px 40px;padding-bottom:80px;}
.grid{display:grid;grid-template-columns:repeat(2,1fr);gap:20px;margin-bottom:25px;}
.card{background:white;border-radius:12px;padding:25px;text-align:center;box-shadow:0 4px 10px rgba(0,0,0,0.08);}
.card i{font-size:30px;margin-bottom:10px;color:#0a8f3c;}
.small-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:25px;}
.orange{color:#ff9800;}
.red{color:#f44336;}
.section-title{font-size:18px;font-weight:600;margin-bottom:15px;}
.btn{display:block;width:100%;background:#0a8f3c;color:white;padding:14px;margin-bottom:12px;border-radius:8px;text-align:center;text-decoration:none;}
.bottom{position:fixed;bottom:0;width:100%;background:white;display:flex;justify-content:space-around;padding:12px;box-shadow:0 -2px 10px rgba(0,0,0,0.1);}
.bottom a{text-decoration:none;color:#777;text-align:center;font-size:14px;}
.bottom .material-icons{display:block;font-size:22px;}
.bottom a.active{color:#009846;font-weight:bold;}
</style>
</head>

<body>

<div class="header">
<h2>
<?php echo ucfirst($role); ?> Dashboard 
</h2>
</div>

<div class="container">

<?php if($role=="principal" && empty($department)){ ?>

<div class="section-title">Select Department</div>

<?php
$q = mysqli_query($conn,"SELECT DISTINCT department FROM students ORDER BY department");

while($row = mysqli_fetch_assoc($q)){
    $d = $row['department'];
    echo "<a href='?department=$d' class='btn'>$d Department</a>";
}
?>

<?php } else { ?>

<div class="grid">
<div class="card">
<i class="material-icons">person</i>
<div>Total Teachers</div>
<h2><?php echo $totalTeachers; ?></h2>
</div>

<div class="card">
<i class="material-icons">school</i>
<div>Total Students</div>
<h2><?php echo $totalStudents; ?></h2>
</div>
</div>

<div class="small-grid">
<div class="card">
<i class="material-icons">arrow_upward</i>
<div>Promoted</div>
<h2><?php echo $promoted; ?></h2>
</div>

<div class="card">
<i class="material-icons orange">trending_up</i>
<div>With ATKT</div>
<h2><?php echo $atkt; ?></h2>
</div>

<div class="card">
<i class="material-icons red">warning</i>
<div>Detained</div>
<h2><?php echo $detained; ?></h2>
</div>
</div>

<div class="section-title">Academic Actions</div>
<a href="student_by_class.php?department=<?php echo $department; ?>" class="btn">View Students</a>
<a href="teacher.php?department=<?php echo $department; ?>" class="btn">View Teachers</a>
<a href="promoted_classes.php?department=<?php echo $department; ?>" class="btn">View Promoted List</a>
<a href="atkt_classes.php?department=<?php echo $department; ?>" class="btn">View ATKT List</a>
<a href="detained_classes.php?department=<?php echo $department; ?>" class="btn">View Detained List</a>

<?php } ?>

</div>

<div class="bottom">
<a href="teacher_dashboard.php" class="active">
<span class="material-icons">dashboard</span>
Dashboard
</a>

<a href="settings.php">
<span class="material-icons">settings</span>
Settings
</a>
</div>

</body>
</html>