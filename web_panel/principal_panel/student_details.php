<?php
require_once(__DIR__ . "/../config.php");
require_once(__DIR__ . "/../promotion_helper.php");

$roll = $_GET['roll'];

/* ================= BASIC DETAILS ================= */
$stmt = $conn->prepare("
SELECT user_id, full_name, roll_no, enrollment_no, mobile_no, parent_mobile_no, class, current_semester 
FROM students 
WHERE roll_no = ?
");
$stmt->bind_param("s", $roll);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Student not found";
    exit;
}

$student = $result->fetch_assoc();
$studentId = $student['user_id'];
$currentSemester = $student['current_semester'];

/* ================= MARKS ================= */
$marksStmt = $conn->prepare("
SELECT subject, exam_type, obtained_marks 
FROM marks 
WHERE student_id = ? AND semester = ?
");
$marksStmt->bind_param("ii", $studentId, $currentSemester);
$marksStmt->execute();
$marksResult = $marksStmt->get_result();

$ct1Marks = [];
$ct2Marks = [];

while($row = $marksResult->fetch_assoc()){
    $mark = $row['obtained_marks'] ?? "Ab";

    if($row['exam_type']=="CT1"){
        $ct1Marks[$row['subject']] = $mark;
    }
    elseif($row['exam_type']=="CT2"){
        $ct2Marks[$row['subject']] = $mark;
    }
}

/* ================= PROMOTION ================= */
$setting = $conn->query("SELECT atkt_limit FROM settings LIMIT 1");
$atktLimit = $setting->fetch_assoc()['atkt_limit'];
$promotion = calculatePromotion($conn, $studentId, $atktLimit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Details</title>

<style>
* { box-sizing:border-box; margin:0; padding:0; font-family:'Inter',sans-serif; }
body { background:#f5f5f5; color:#333; }

.app-header {
    background:#008139;
    color:white;
    padding:15px 20px;
    display:flex;
    align-items:center;
}

.app-header h1 {
    font-size:20px;
    margin-left:20px;
}

.container { padding:15px; }

.card {
    background:#fff;
    border-radius:18px;
    padding:20px;
    margin-bottom:15px;
    box-shadow:0 2px 8px rgba(0,0,0,0.05);
}

.card h3 {
    font-size:16px;
    margin-bottom:15px;
    font-weight:700;
}

.profile-main { text-align:center; margin-top:20px; }

/* ✅ UPDATED PROFILE ICON */
.avatar-circle {
    width:80px;
    height:80px;
    background:#008139;
    border-radius:50%;
    margin:auto;
    display:flex;
    align-items:center;
    justify-content:center;
}

.user-icon {
    font-size:36px;
    color:white;
}

.profile-main h2 { margin:10px 0 5px; }

.data-row {
    display:flex;
    justify-content:space-between;
    padding:10px 0;
}

.label { font-size:14px; }
.value { font-weight:600; font-size:14px; }

.subject-name { flex:1; }
.mark-badge { color:#008139; font-weight:700; }

.status-text { font-weight:700; }

.empty { text-align:center; color:#777; }
</style>

</head>

<body>

<div class="app-header">
    <span>←</span>
    <h1>Student Details</h1>
</div>

<div class="container">

<!-- PROFILE -->
<div class="card profile-main">
    <div class="avatar-circle">
        <span class="user-icon">👨‍🎓</span>
    </div>
    <h2><?php echo $student['full_name']; ?></h2>
    <p>Roll No: <?php echo $student['roll_no']; ?></p>
</div>

<!-- ACADEMIC -->
<div class="card">
    <h3>Academic Information</h3>
    <div class="data-row">
        <span class="label">Enrollment No</span>
        <span class="value"><?php echo $student['enrollment_no']; ?></span>
    </div>
    <div class="data-row">
        <span class="label">Semester</span>
        <span class="value"><?php echo $currentSemester; ?></span>
    </div>
</div>

<!-- CONTACT -->
<div class="card">
    <h3>Contact Information</h3>
    <div class="data-row">
        <span class="label">Student Mobile</span>
        <span class="value"><?php echo $student['mobile_no']; ?></span>
    </div>
    <div class="data-row">
        <span class="label">Parent Mobile</span>
        <span class="value"><?php echo $student['parent_mobile_no']; ?></span>
    </div>
</div>

<!-- PROMOTION -->
<div class="card">
    <h3>Promotion Status</h3>
    <div class="data-row">
        <span class="label">Status</span>
        <span class="value status-text"><?php echo $promotion['status']; ?></span>
    </div>
    <div class="data-row">
        <span class="label">Backlogs</span>
        <span class="value"><?php echo $promotion['backlogCount']; ?></span>
    </div>
</div>

<!-- CT1 -->
<div class="card">
    <h3>CT1 Marks</h3>
    <?php if(!empty($ct1Marks)){ ?>
        <?php foreach($ct1Marks as $sub=>$mark){ ?>
        <div class="data-row">
            <span class="label subject-name"><?php echo $sub; ?></span>
            <span class="value mark-badge"><?php echo $mark; ?></span>
        </div>
        <?php } ?>
    <?php } else { echo "<div class='empty'>No data</div>"; } ?>
</div>

<!-- CT2 -->
<div class="card">
    <h3>CT2 Marks</h3>
    <?php if(!empty($ct2Marks)){ ?>
        <?php foreach($ct2Marks as $sub=>$mark){ ?>
        <div class="data-row">
            <span class="label subject-name"><?php echo $sub; ?></span>
            <span class="value mark-badge"><?php echo $mark; ?></span>
        </div>
        <?php } ?>
    <?php } else { echo "<div class='empty'>No data</div>"; } ?>
</div>

</div>

</body>
</html>