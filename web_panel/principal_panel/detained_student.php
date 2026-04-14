<?php
require_once("../config.php");
require_once("../promotion_helper.php");
//student
if(!isset($_GET['class'])){
    die("Class not selected");
}

$class = $_GET['class'];

/* ================= GET SETTINGS ================= */
$settingRes = $conn->query("SELECT atkt_limit FROM settings LIMIT 1");
if(!$settingRes || $settingRes->num_rows == 0){
    die("Settings not found");
}
$settings = $settingRes->fetch_assoc();
$atktLimit = (int)$settings['atkt_limit'];

/* ================= GET DETAINED STUDENTS ================= */
$stmt = $conn->prepare("
    SELECT user_id, full_name
    FROM students
    WHERE class = ?
    AND status = 'detained'
");
$stmt->bind_param("s", $class);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
<title>Detained Students - <?php echo htmlspecialchars($class); ?></title>
<style>
body{margin:0;font-family:Arial;background:#e9e4ea;}
.header{background:#0a8f3c;color:white;padding:18px;font-size:22px;}
.container{padding:15px;}
.student-card{
background:white;padding:18px;margin-bottom:15px;
border-radius:12px;box-shadow:0 2px 6px rgba(0,0,0,0.2);
}
.name{font-size:18px;font-weight:bold;}
.status{margin-top:8px;}
.empty{
text-align:center;font-size:16px;color:#555;
background:white;padding:20px;border-radius:12px;
}
.kt-subjects{margin-top:5px;color:red;font-size:14px;}
</style>
</head>
<body>

<div class="header">
Detained Students - <?php echo htmlspecialchars($class); ?>
</div>

<div class="container">

<?php
$displayed = false;

if($result && $result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        $studentId = $row['user_id'];

        // calculate promotion status
        $promotion = calculatePromotion($conn, $studentId, $atktLimit);

        // Only display DETAINED students
        if($promotion['status'] !== "DETAINED"){
            continue;
        }

        $displayed = true;
?>
<div class="student-card">
    <div class="name"><?php echo htmlspecialchars($row['full_name']); ?></div>
    <div class="status">
        Status: <?php echo $promotion['status']; ?>
        <?php if($promotion['percentage'] !== null){ ?>
            | Percentage: <?php echo number_format($promotion['percentage'],2); ?>%
        <?php } ?>
    </div>
    <?php if($promotion['backlogCount'] > 0){ ?>
        <div class="kt-subjects">
            Backlogs (<?php echo $promotion['backlogCount']; ?>): <?php echo implode(", ", $promotion['ktSubjects']); ?>
        </div>
    <?php } ?>
</div>
<?php
    }
}

if(!$displayed){
    echo "<div class='empty'>No detained students found for this class.</div>";
}
?>

</div>
</body>
</html>