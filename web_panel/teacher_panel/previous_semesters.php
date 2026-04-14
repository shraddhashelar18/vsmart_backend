<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../config.php");
session_start();

/* LOGIN CHECK */
if(!isset($_SESSION['teacher_id'])){
    header("Location: ../auth_panel/login.php");
    exit();
}

$student_id = $_GET['user_id'] ?? '';

if(!$student_id){
    die("Student ID missing");
}

/* GET CURRENT SEM */
$stmt = $conn->prepare("
SELECT current_semester, full_name 
FROM students 
WHERE user_id=?
");
$stmt->bind_param("i",$student_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

$current_sem = $row['current_semester'];
$name = $row['full_name'];

/* SELECTED SEM */
$selected_sem = $_GET['sem'] ?? ($current_sem - 1);

if($selected_sem < 1){
    $selected_sem = 1;
}

/* =========================
   FETCH MARKS
========================= */
$stmt = $conn->prepare("
SELECT subject, exam_type, obtained_marks, total_marks
FROM marks
WHERE student_id=? AND semester=? AND status='published'
");
$stmt->bind_param("ii",$student_id,$selected_sem);
$stmt->execute();
$result = $stmt->get_result();

$totalObt = 0;
$totalMax = 0;
$subjects = [];

while($r = $result->fetch_assoc()){

    $subject = $r['subject'];
    $exam = strtoupper($r['exam_type']);

    if(!isset($subjects[$subject])){
        $subjects[$subject] = [];
    }

    $subjects[$subject][$exam] = $r['obtained_marks'];

    $totalObt += $r['obtained_marks'];
    $totalMax += $r['total_marks'];
}
/* =========================
   FETCH SEM RESULT %
========================= */
$stmt = $conn->prepare("
SELECT percentage 
FROM semester_results 
WHERE student_id=? AND semester=?
LIMIT 1
");

$stmt->bind_param("ii", $student_id, $selected_sem);
$stmt->execute();
$res = $stmt->get_result();

if($res->num_rows > 0){
    $row = $res->fetch_assoc();
    $percentage = $row['percentage'];
} else {
    $percentage = 0; // or fallback if needed
}
/* RESULT */
$resultStatus = $percentage >= 40 ? "PASS" : "FAIL";
/* =========================
   FETCH MARKSHEET PDF
========================= */
$marksheetPdf = null;

$pdfStmt = $conn->prepare("
SELECT marksheet_pdf 
FROM semester_results 
WHERE student_id=? AND semester=?
LIMIT 1
");

if($pdfStmt){
    $pdfStmt->bind_param("ii", $student_id, $selected_sem);
    $pdfStmt->execute();
    $pdfRes = $pdfStmt->get_result();

    if($pdfRes->num_rows > 0){
        $pdfRow = $pdfRes->fetch_assoc();
        $marksheetPdf = $pdfRow['marksheet_pdf'];
    }
}
/* =========================
   ATTENDANCE %
========================= */
$stmt = $conn->prepare("
SELECT status FROM attendance 
WHERE student_id=? AND semester=?
");
$stmt->bind_param("ii",$student_id,$selected_sem);
$stmt->execute();
$attRes = $stmt->get_result();

$totalDays = $attRes->num_rows;
$present = 0;

while($a = $attRes->fetch_assoc()){
    if($a['status'] == 'P'){
        $present++;
    }
}

$attendance = $totalDays > 0 ? round(($present/$totalDays)*100,2) : 0;

/* =========================
   ATTENDANCE TREND (GRAPH)
========================= */
$stmt = $conn->prepare("
SELECT 
    MONTHNAME(date) as month,
    MONTH(date) as month_num,
    SUM(CASE WHEN status='P' THEN 1 ELSE 0 END) as present,
    COUNT(*) as total
FROM attendance
WHERE student_id=? AND semester=?
GROUP BY MONTH(date)
ORDER BY 
    CASE 
        WHEN MONTH(date) >= 11 THEN MONTH(date)   -- Nov, Dec
        ELSE MONTH(date) + 12                     -- Jan → June becomes 13–18
    END
");

$stmt->bind_param("ii", $student_id, $selected_sem);
$stmt->execute();
$trendRes = $stmt->get_result();

// ✅ Dynamic month order based on semester
if($selected_sem % 2 == 1){
    // ODD SEM → June to November
    $allMonths = ["June","July","August","September","October","November"];
}else{
    // EVEN SEM → December to May
    $allMonths = ["December","January","February","March","April","May"];
}
$dataMap = [];

// store DB data
while($t = $trendRes->fetch_assoc()){
    $dataMap[$t['month']] = round(($t['present']/$t['total'])*100,2);
}

// build final arrays (fill missing months)
$months = [];
$percentages = [];

foreach($allMonths as $m){
    $months[] = $m;
    $percentages[] = $dataMap[$m] ?? 0; // 0 if no data
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Previous Semesters</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<!-- CHART JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body{
margin:0;
font-family:Arial;
background:#f3f3f3;
}

.header{
background:#009846;
color:white;
padding:18px;
display:flex;
align-items:center;
font-size:20px;
}

.back{
margin-right:10px;
cursor:pointer;
}

.select{
margin:20px;
}

select{
width:100%;
padding:10px;
border-radius:8px;
border:none;
background:#eee;
}

.summary{
background:#009846;
color:white;
margin:20px;
padding:20px;
border-radius:12px;
}
a{
    text-decoration: none;
}
.summary p{
margin:6px 0;
}

.section{
margin:20px;
font-weight:bold;
}

.card{
background:white;
margin:10px 20px;
padding:15px;
border-radius:12px;
box-shadow:0 2px 6px rgba(0,0,0,0.08);
}

.row{
display:flex;
justify-content:space-between;
margin:5px 0;
}
</style>
</head>

<body>

<div class="header">
<span class="material-icons back" onclick="goBack()">
arrow_back
</span>
Previous Semesters
</div>

<div class="select">
<select onchange="changeSem(this.value)">
<?php for($i=1;$i<$current_sem;$i++){ ?>
<option value="<?= $i ?>" <?= $i==$selected_sem?'selected':'' ?>>
Sem <?= $i ?>
</option>
<?php } ?>
</select>
</div>

<div class="summary">
<p><b>Sem <?= $selected_sem ?></b></p>
<p>Overall: <?= $percentage ?>%</p>
<p>Attendance: <?= $attendance ?>%</p>
<p>Result: <?= $resultStatus ?></p>
</div>

<div class="section">Attendance Trend</div>

<!-- ✅ SIZE FIX APPLIED -->
<div style="height:180px; margin:20px;">
    <canvas id="attendanceChart"></canvas>
</div>

<div class="section">Exam Performance</div>

<?php foreach($subjects as $sub => $exams){ ?>

<div class="card">
<b><?= $sub ?></b>

<?php foreach($exams as $exam => $mark){ ?>
<div class="row">
<span><?= $exam ?></span>
<span><?= $mark ?></span>
</div>
<?php } ?>

</div>

<?php } ?>

<script>
function changeSem(sem){
    window.location.href = "?user_id=<?= $student_id ?>&class=<?= $_GET['class'] ?>&sem=" + sem;
}

const ctx = document.getElementById('attendanceChart').getContext('2d');

/* LIGHT GREEN GRADIENT (LIKE APP) */
const gradient = ctx.createLinearGradient(0, 0, 0, 200);
gradient.addColorStop(0, "rgba(0,150,70,0.25)");
gradient.addColorStop(1, "rgba(0,150,70,0.02)");

new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($months) ?>,
datasets: [{
    data: <?= json_encode($percentages) ?>,
    fill: true,
    backgroundColor: gradient,
    borderColor: "#009846",
    borderWidth: 2,

    tension: 0.2, // ✅ small smooth (NOT wave)

    pointRadius: 4,
    pointBackgroundColor: "#009846",
    pointBorderWidth: 0
}]
    },
    options: {
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            x: {
                grid: {
                    color: "rgba(0,0,0,0.1)",
                    borderDash: [5,5]   // ✅ dotted vertical lines
                },
                ticks: {
                    color: "#666"
                }
            },
            y: {
                min: 0,
                max: 100,
                ticks: {
                    stepSize: 20,
                    color: "#666"
                },
                grid: {
                    color: "rgba(0,0,0,0.1)",
                    borderDash: [5,5]   // ✅ dotted horizontal lines
                }
            }
        }
    }
});
function goBack(){
    window.location.href = "student_report.php?user_id=<?= $_GET['user_id'] ?>&class=<?= $_GET['class'] ?>";
}
</script>
<?php if(!empty($marksheetPdf)){ ?>

<a href="/vsmart/<?= $marksheetPdf ?>" target="_blank" style="text-decoration:none;">
    <button style="
        width:90%;
        margin:20px auto;
        display:block;
        padding:14px;
        background:#009846;
        color:white;
        border:none;
        border-radius:12px;
        font-size:16px;
        font-weight:bold;
        cursor:pointer;
    ">
        View Final Marksheet
    </button>
</a>

<?php } else { ?>

<button onclick="alert('Marksheet not available')" style="
    width:90%;
    margin:20px auto;
    display:block;
    padding:14px;
    background:#ccc;
    color:#333;
    border:none;
    border-radius:12px;
    font-size:16px;
    font-weight:bold;
">
    View Final Marksheet
</button>

<?php } ?>
</body>
</html>