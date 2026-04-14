<?php
require_once("../config.php");
require_once("../promotion_helper.php");
//student
/* ================= GET CLASS ================= */

if(!isset($_GET['class'])){
    die("Class not selected");
}

$class = $_GET['class'];

/* ================= ATKT LIMIT ================= */

$setting = $conn->query("SELECT atkt_limit FROM settings LIMIT 1");
$atktLimit = (int)$setting->fetch_assoc()['atkt_limit'];

/* ================= GET STUDENTS ================= */

$stmt = $conn->prepare("
    SELECT user_id, full_name
    FROM students
    WHERE class = ?
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

.header{
    background:#0aa03c;
    color:#fff;
    padding:16px;
    font-size:20px;
    font-weight:600;
    display:flex;
    align-items:center;
}

.back{
    font-size:22px;
    margin-right:10px;
    cursor:pointer;
}

.container{
    padding:15px;
}

.card{
    background:#f3f1f5;
    border-radius:15px;
    padding:15px;
    margin-bottom:15px;
    box-shadow:0 2px 6px rgba(0,0,0,0.15);
}

.name{
    font-size:18px;
    font-weight:600;
    color:#333;
}

.text{
    font-size:14px;
    margin-top:5px;
    color:#444;
}

.kt{
    color:#e67e22;
    font-weight:600;
}

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

    $promotion = calculatePromotion($conn, $row['user_id'], $atktLimit);

    /* ✅ ONLY SHOW ATKT STUDENTS */
    if(strtolower($promotion['status']) != "promoted_with_atkt"){
        continue;
    }

    $found = true;

    $backlogs = $promotion['backlogCount'] ?? 0;
    $subjects = $promotion['ktSubjects'] ?? [];
?>

<div class="card">

    <div class="name">
        <?php echo htmlspecialchars($row['full_name']); ?>
    </div>

    <div class="text">
        Backlogs: <?php echo $backlogs; ?>
    </div>

    <div class="text kt">
        KT Subjects: 
        <?php echo !empty($subjects) ? implode(", ", $subjects) : "None"; ?>
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