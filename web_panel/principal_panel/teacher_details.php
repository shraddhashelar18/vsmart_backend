<?php
require_once("../config.php");

$id = $_GET['id'];

/* =====================
TEACHER BASIC DETAILS
===================== */
$stmt = $conn->prepare("
SELECT 
t.user_id,
t.full_name,
t.mobile_no,
u.email
FROM teachers t
JOIN users u ON t.user_id = u.user_id
WHERE t.user_id = ?
");

$stmt->bind_param("i",$id);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();

/* =====================
ASSIGNMENTS
===================== */
$assignStmt = $conn->prepare("
SELECT class, subject, department
FROM teacher_assignments
WHERE user_id = ?
AND status='active'
");

$assignStmt->bind_param("i",$id);
$assignStmt->execute();
$assignResult = $assignStmt->get_result();

$assignments = [];
while($row = $assignResult->fetch_assoc()){
    $assignments[] = $row;
}

/* =====================
CLASS TEACHER
===================== */
$classStmt = $conn->prepare("
SELECT class_name
FROM classes
WHERE class_teacher = ?
");

$classStmt->bind_param("i",$id);
$classStmt->execute();
$classResult = $classStmt->get_result();

$isClassTeacher = false;
$classTeacherOf = "";

if($classResult->num_rows > 0){
    $isClassTeacher = true;
    $row = $classResult->fetch_assoc();
    $classTeacherOf = $row['class_name'];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Teacher Profile</title>

<style>

/* ===== BODY ===== */
body{
    margin:0;
    font-family:'Segoe UI', sans-serif;
    background:#e9e4ea;
}

/* ===== PROFILE CONTAINER ===== */
.profile-container{
    padding:15px;
}

/* ===== TOP CARD ===== */
.profile-card{
    background:#f3f1f5;
    border-radius:18px;
    padding:25px;
    text-align:center;
    box-shadow:0 3px 8px rgba(0,0,0,0.1);
}

/* ===== AVATAR ===== */
.profile-avatar{
    width:60px;
    height:60px;
    background:#dff3e6;
    border-radius:50%;
    margin:auto;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:26px;
    color:#0b8f3a;
    margin-bottom:10px;
}

/* ===== NAME ===== */
.profile-name{
    font-size:18px;
    font-weight:600;
    color:#333;
}

/* ===== COMMON CARD ===== */
.card{
    background:#f3f1f5;
    border-radius:14px;
    padding:18px;
    margin:15px;
    box-shadow:0 3px 8px rgba(0,0,0,0.1);
}

/* ===== TITLE ===== */
.card-title{
    font-size:15px;
    font-weight:600;
    margin-bottom:10px;
    color:#444;
}

/* ===== ROW ===== */
.row{
    display:flex;
    justify-content:space-between;
    padding:6px 0;
    font-size:14px;
    color:#333;
}

/* ===== BACK BUTTON ===== */
.back{
    padding:10px 15px;
    font-size:18px;
    cursor:pointer;
}

</style>

</head>

<body>

<!-- BACK BUTTON -->
<div class="back" onclick="history.back()">← Back</div>

<!-- PROFILE -->
<div class="profile-container">
    <div class="profile-card">

        <div class="profile-avatar">👨‍🏫</div>

        <div class="profile-name">
            <?php echo htmlspecialchars($teacher['full_name']); ?>
        </div>

    </div>
</div>

<!-- CONTACT -->
<div class="card">
    <div class="card-title">Contact Information</div>

    <div class="row">
        <span>Mobile</span>
        <span><?php echo htmlspecialchars($teacher['mobile_no']); ?></span>
    </div>

    <div class="row">
        <span>Email</span>
        <span><?php echo htmlspecialchars($teacher['email']); ?></span>
    </div>
</div>

<!-- ASSIGNMENTS -->
<div class="card">
    <div class="card-title">Teaching Assignments</div>

    <?php foreach($assignments as $a){ ?>
    <div class="row">
        <span><?php echo htmlspecialchars($a['class']); ?></span>
        <span><?php echo htmlspecialchars($a['subject']); ?></span>
    </div>
    <?php } ?>

</div>

<!-- CLASS TEACHER -->
<?php if($isClassTeacher){ ?>
<div class="card">
    <div class="card-title">Class Teacher</div>

    <div class="row">
        <span>Assigned Class</span>
        <span><?php echo htmlspecialchars($classTeacherOf); ?></span>
    </div>
</div>
<?php } ?>

</body>
</html>