<?php
require_once("../config.php");

/* ================= GET CLASS ================= */

if(!isset($_GET['class'])){
    die("Class not selected");
}

$class = $_GET['class'];

/* ================= GET ATKT STUDENTS ================= */

$stmt = $conn->prepare("
    SELECT user_id, full_name
    FROM students
    WHERE class = ? AND status = 'promoted_with_atkt'
");

$stmt->bind_param("s", $class);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
<title>Promoted With KT</title>

<style>

body{
    margin:0;
    font-family: 'Segoe UI', Arial;
    background:#e6e3e7;
}

/* HEADER */
.header{
    background:#0aa03c;
    color:#fff;
    padding:16px;
    font-size:20px;
    font-weight:600;
    display:flex;
    align-items:center;
}

/* BACK BUTTON */
.back{
    font-size:22px;
    margin-right:10px;
    cursor:pointer;
}

/* CONTAINER */
.container{
    padding:15px;
}

/* CARD */
.card{
    background:#f3f1f5;
    border-radius:15px;
    padding:15px;
    margin-bottom:15px;
    box-shadow:0 2px 6px rgba(0,0,0,0.15);
}

/* NAME */
.name{
    font-size:18px;
    font-weight:600;
    color:#333;
}

/* TEXT */
.text{
    font-size:14px;
    margin-top:5px;
    color:#444;
}

/* KT SUBJECT COLOR */
.kt{
    color:#f39c12;
    font-weight:600;
}

/* EMPTY */
.empty{
    text-align:center;
    margin-top:50px;
    font-size:16px;
    color:#666;
}

</style>

</head>

<body>

<div class="header">
    <span class="back" onclick="history.back()">←</span>
    <?php echo htmlspecialchars($class); ?> - Promoted With KT
</div>

<div class="container">

<?php
$found = false;

while($row = $result->fetch_assoc()){
    $found = true;

    /* 🔥 TEMP STATIC DATA (Replace later with real logic) */
    $backlogs = 1;
    $subjects = ["Physics"];
?>

<div class="card">

    <div class="name">
        <?php echo htmlspecialchars($row['full_name']); ?>
    </div>

    <div class="text">
        Backlogs: <?php echo $backlogs; ?>
    </div>

    <div class="text kt">
        KT Subjects: <?php echo implode(", ", $subjects); ?>
    </div>

</div>

<?php
}

if(!$found){
    echo "<div class='empty'>No ATKT students found</div>";
}
?>

</div>

</body>
</html>