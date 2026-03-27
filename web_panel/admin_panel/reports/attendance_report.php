<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../../config.php");

/* ===============================
GET ACTIVE SEMESTER (EVEN / ODD)
=============================== */

$result = $conn->query("SELECT active_semester FROM settings LIMIT 1");

if(!$result){
    die("Query Error: " . $conn->error);
}

$settings = $result->fetch_assoc();

if(!$settings){
    die("Settings row missing");
}

$cycle = $settings['active_semester'];

if($cycle=="EVEN"){
$semFilter="semester IN (2,4,6)";
}else{
$semFilter="semester IN (1,3,5)";
}

/* ===============================
MONTHS BASED ON SEMESTER
=============================== */

if($cycle=="EVEN"){

$months=[
"December",
"January",
"February",
"March",
"April",
"May",
"June"
];

}else{

$months=[
"July",
"August",
"September",
"October",
"November"
];

}

$currentMonth = date("n");

/* semester months order */
$evenMonths = [12, 1, 2, 3, 4, 5];
$oddMonths  = [6, 7, 8, 9, 10, 11];

/* pick correct order */
$monthOrder = ($cycle == "EVEN") ? $evenMonths : $oddMonths;

/* find current index */
$currentIndex = array_search($currentMonth, $monthOrder);

/* ===============================
GET DEPARTMENTS
=============================== */

$departments=$conn->query("SELECT DISTINCT department FROM classes");

$selectedDept=$_GET['department'] ?? '';
$selectedClass=$_GET['class'] ?? '';
$selectedMonth=$_GET['month'] ?? '';

$classes=[];
$students=[];

/* ===============================
LOAD CLASSES
=============================== */

if($selectedDept){

$stmt=$conn->prepare("
SELECT class_name
FROM classes
WHERE department=? AND $semFilter
ORDER BY semester
");

$stmt->bind_param("s",$selectedDept);
$stmt->execute();

$classes=$stmt->get_result();

}

/* ===============================
LOAD STUDENT ATTENDANCE
=============================== */

if($selectedClass && $selectedMonth){

$monthNumber=date("n",strtotime($selectedMonth));

$stmt = $conn->prepare("
SELECT 
s.full_name,
COUNT(a.id) AS total,
SUM(CASE WHEN a.status='P' THEN 1 ELSE 0 END) AS present
FROM students s
LEFT JOIN attendance a
ON s.user_id = a.student_id
AND a.class = ?
AND MONTH(a.date) = ?
WHERE s.class = ?
GROUP BY s.user_id
ORDER BY s.full_name
");

$stmt->bind_param("sis", $selectedClass, $monthNumber, $selectedClass);
$stmt->execute();

$res=$stmt->get_result();

while($row=$res->fetch_assoc()){

$total=$row['total'];
$present=$row['present'];

$percent = ($total > 0) ? round(($present/$total)*100) : 0;

$row['percent']=$percent;

$students[]=$row;

}

}
?>

<!DOCTYPE html>
<html>
<head>

<title>Attendance Report</title>

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
font-size:20px;
display:flex;
align-items:center;
gap:10px;
box-shadow:0 2px 6px rgba(0,0,0,0.15);
}

/* MAIN CONTAINER */

.container{
max-width:900px;
margin:50px auto;
padding:30px;
background:white;
border-radius:12px;
box-shadow:0 6px 18px rgba(0,0,0,0.08);
}

/* FIELD */

.field{
margin-bottom:18px;
}

.field label{
font-size:14px;
color:#666;
display:block;
margin-bottom:6px;
}

/* DROPDOWN */

.select{
width:100%;
padding:14px;
border-radius:12px;
border:none;
background:#f2f2f2;
font-size:15px;
outline:none;
}

/* STUDENT TITLE */

.title{
font-size:18px;
font-weight:600;
margin-top:25px;
margin-bottom:15px;
}

/* STUDENT CARD */

.card{

background:white;
border-radius:12px;
padding:18px;
margin-bottom:14px;

display:flex;
justify-content:space-between;
align-items:center;

box-shadow:0 4px 10px rgba(0,0,0,0.08);

transition:0.2s;

}

.card:hover{
transform:translateY(-2px);
box-shadow:0 6px 14px rgba(0,0,0,0.12);
}

/* STUDENT LEFT SIDE */

.student{
display:flex;
align-items:center;
gap:14px;
}

/* AVATAR */

.avatar{
width:50px;
height:50px;
border-radius:50%;
background:#EAF7F1;

display:flex;
align-items:center;
justify-content:center;

color:#009846;
font-size:24px;
}

/* NAME */

.name{
font-size:18px;
font-weight:600;
}

/* PRESENT TEXT */

.sub{
font-size:14px;
color:#666;
}

/* PERCENT */

.percent{
font-size:18px;
font-weight:bold;
}

.green{
color:#009846;
}

.red{
color:#e53935;
}
</style>

</head>

<body>

<div class="topbar">

<a href="../reports.php" style="color:white;text-decoration:none;display:flex;align-items:center;gap:6px;">
<span class="material-icons">arrow_back</span>
</a>
Attendance Report
</div>

<div class="container">

<form method="GET">

<div class="field">

<label>Department</label>

<select class="select" name="department" onchange="this.form.submit()">

<option value="">Select</option>

<?php while($d=$departments->fetch_assoc()): ?>

<option value="<?=$d['department']?>" 
<?=$selectedDept==$d['department']?'selected':''?>>

<?=$d['department']?>

</option>

<?php endwhile; ?>

</select>

</div>


<div class="field">

<label>Class</label>

<select class="select" name="class" onchange="this.form.submit()">

<option value="">Select</option>

<?php if($classes) while($c=$classes->fetch_assoc()): ?>

<option value="<?=$c['class_name']?>"
<?=$selectedClass==$c['class_name']?'selected':''?>>

<?=$c['class_name']?>

</option>

<?php endwhile; ?>

</select>

</div>


<div class="field">

<label>Month</label>

<select class="select" name="month" onchange="this.form.submit()">

<option value="">Select</option>

<?php foreach($months as $m): 

    $monthNumber = date("n", strtotime($m));
    $monthIndex = array_search($monthNumber, $monthOrder);

    $enabled = false;

    if($monthIndex !== false && $currentIndex !== false){
        $enabled = $monthIndex < $currentIndex;
    }
?>

<option value="<?=$m?>"
<?=$selectedMonth==$m?'selected':''?>
data-enabled="<?=$enabled ? '1' : '0'?>"
style="<?=$enabled ? '' : 'color:gray;'?>">

<?=$m?>

</option>

<?php endforeach; ?>

</select>

</div>

</form>

<div class="title">Students</div>

<?php if(empty($students)){ ?>

<p style="color:#777;font-size:15px;">No attendance data available</p>

<?php } else { ?>

<?php foreach($students as $s): ?>

<div class="card">

<div class="student">

<div class="avatar">
<span class="material-icons">person</span>
</div>

<div>

<div class="name"><?=$s['full_name']?></div>

<div class="sub">
Present: <?=$s['present']?> / <?=$s['total']?> lectures
</div>

</div>

</div>

<div class="percent <?=$s['percent']>=75?'green':'red'?>">
<?=$s['percent']?>%
</div>

</div>

<?php endforeach; ?>

<?php } ?>

</div>
<script>
document.querySelector("select[name='month']").addEventListener("change", function(){

    let selectedOption = this.options[this.selectedIndex];
    let isEnabled = selectedOption.getAttribute("data-enabled");

    if(isEnabled === "0"){
        alert("This month is not available yet");

        // reset selection
        this.selectedIndex = 0;
        return;
    }

    // submit form only if valid
    this.form.submit();
});
</script>
</body>
</html>