<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../config.php");
session_start();

if(!isset($_SESSION['teacher_id'])){
    header("Location: ../auth_panel/login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

$class = $_GET['class'] ?? '';
$subject = $_GET['subject'] ?? '';
$examType = $_POST['exam_type'] ?? 'CT1';
$totalMarks = 30;

/* FETCH STUDENTS */
$students = $conn->query("
SELECT user_id, full_name, roll_no 
FROM students 
WHERE class = '$class'
");

/* FETCH MARKS */
$existingMarks = [];
$isPublished = false;

$marksRes = $conn->query("
SELECT student_id, obtained_marks, status 
FROM marks 
WHERE class = '$class'
AND subject = '$subject'
AND exam_type = '$examType'
");

while($m = $marksRes->fetch_assoc()){
    $existingMarks[$m['student_id']] = $m['obtained_marks'];

    if($m['status'] == "published"){
        $isPublished = true;
    }
}

/* CALCULATE STATS */
$completed = 0;
$sum = 0;

foreach($existingMarks as $m){
   if($m !== NULL && $m > 0){
    $completed++;
    $sum += $m;
}
}

$totalStudents = $students->num_rows;
$avg = $completed ? round($sum/$completed,1) : 0;
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Enter Marks</title>

<style>
body{
margin:0;
font-family:Segoe UI;
background:#f4f6f9;
}

.header{
background:#009846;
color:white;
padding:16px;
font-size:20px;
}

.container{
padding:16px;
padding-bottom:140px;
}

/* CARD */
.card{
background:white;
border-radius:16px;
padding:16px;
margin-bottom:16px;
box-shadow:0 3px 8px rgba(0,0,0,0.08);
}

.label{
font-weight:600;
margin-bottom:4px;
}

/* STATS */
.stats{
display:flex;
gap:10px;
margin-bottom:16px;
}

.stat{
flex:1;
background:white;
border-radius:14px;
padding:14px;
text-align:center;
box-shadow:0 3px 8px rgba(0,0,0,0.08);
}

.value{
font-weight:bold;
font-size:18px;
}

.title{
color:#777;
font-size:13px;
}

/* BUTTONS */
.rowBtns{
display:flex;
gap:10px;
margin-bottom:16px;
}

.btn{
flex:1;
padding:12px;
border-radius:30px;
font-size:14px;
cursor:pointer;
}

.btn-outline{
border:1px solid #009846;
color:#009846;
background:white;
}

.btn-filled{
background:#009846;
color:white;
border:none;
}

.btn-disabled{
background:#ccc !important;
color:#666 !important;
cursor:not-allowed;
}

/* STUDENTS */
.student{
background:white;
border-radius:14px;
padding:14px;
margin-bottom:12px;
display:flex;
justify-content:space-between;
align-items:center;
box-shadow:0 2px 6px rgba(0,0,0,0.08);
}

.student-name{
font-weight:600;
}

.student-roll{
color:#777;
font-size:13px;
}

input{
width:85px;
padding:8px;
text-align:center;
border:none;
border-bottom:2px solid #ccc;
outline:none;
}

/* FOOTER */
.footer{
position:fixed;
bottom:0;
left:0;
width:100%;
background:white;
padding:16px;
box-shadow:0 -2px 8px rgba(0,0,0,0.08);
}

.footer button{
width:100%;
padding:14px;
border-radius:30px;
margin-top:10px;
font-size:15px;
}

.draft{
background:white;
border:1px solid #ccc;
}

.publish{
background:#009846;
color:white;
}

/* SNACKBAR */
#snackbar {
visibility:hidden;
background:#333;
color:white;
padding:14px;
position:fixed;
bottom:20px;
left:50%;
transform:translateX(-50%);
border-radius:8px;
}

#snackbar.show{
visibility:visible;
}
</style>
</head>

<body>

<div class="header">Enter Marks</div>

<div class="container">

<form method="POST">

<div class="card">
<div class="label">Class</div>
<?= $class ?><br><br>

<div class="label">Subject</div>
<?= $subject ?><br><br>

<div class="label">Exam Type</div>
<select name="exam_type" onchange="this.form.submit()">
<option value="CT1" <?=($examType=='CT1')?'selected':''?>>CT1</option>
<option value="CT2" <?=($examType=='CT2')?'selected':''?>>CT2</option>
</select>
</div>

<div class="stats">
<div class="stat">
<div class="value"><?= $totalMarks ?></div>
<div class="title">Max Marks</div>
</div>

<div class="stat">
<div class="value"><?= $completed ?>/<?= $totalStudents ?></div>
<div class="title">Completed</div>
</div>

<div class="stat">
<div class="value"><?= $avg ?></div>
<div class="title">Average</div>
</div>
</div>

<div class="rowBtns">
<button type="button" class="btn btn-outline" onclick="downloadTemplate()">
Download Template
</button>

<button type="button" class="btn btn-filled <?= $isPublished?'btn-disabled':'' ?>">
Upload Excel
</button>
</div>

<b>Students (<?= $totalStudents ?>)</b><br><br>

<?php 
$students->data_seek(0);
while($row = $students->fetch_assoc()){ 
?>

<div class="student">
<div>
<div class="student-name"><?= $row['full_name'] ?></div>
<div class="student-roll">Roll No: <?= $row['roll_no'] ?></div>
</div>

<input type="number"
name="marks[<?= $row['user_id'] ?>]"
value="<?= $existingMarks[$row['user_id']] ?? '' ?>"
<?= $isPublished ? 'disabled' : '' ?>
max="30">

<input type="hidden" name="semester[<?= $row['user_id'] ?>]"
value="<?= preg_replace('/[^0-9]/','',$class) ?>">

</div>

<?php } ?>

<div class="footer">
<button name="save_draft" class="draft" <?= $isPublished?'disabled':'' ?>>
Save Draft
</button>

<button name="publish" class="publish <?= $isPublished?'btn-disabled':'' ?>">
Publish Marks
</button>
</div>

</form>

</div>

<div id="snackbar"></div>

<script>
function downloadTemplate(){

let data = "Roll No,Marks\n";

document.querySelectorAll(".student").forEach(row=>{
let roll = row.querySelector(".student-roll").innerText.replace("Roll No: ","");
data += roll + ",\n";
});

let blob = new Blob([data], {type:"text/csv"});
let a = document.createElement("a");
a.href = URL.createObjectURL(blob);
a.download = "<?= $class ?>_<?= str_replace(' ','_',$subject) ?>_<?= $examType ?>.csv";
a.click();

showSnackbar("Template saved to Downloads");
}

function showSnackbar(msg){
let x = document.getElementById("snackbar");
x.innerText = msg;
x.className = "show";

setTimeout(()=>{ x.className=""; },3000);
}
</script>

</body>
</html>

<?php

/* SAVE LOGIC */

if(isset($_POST['publish']) || isset($_POST['save_draft'])){

$isDraft = isset($_POST['save_draft']);

foreach($_POST['marks'] as $sid=>$marks){

$semester = $_POST['semester'][$sid];

if($marks==="") $marks=NULL;

$status = $isDraft ? "draft":"published";

$check = $conn->query("
SELECT id FROM marks
WHERE student_id='$sid' AND subject='$subject' AND exam_type='$examType' AND class='$class'
");

if($check->num_rows>0){

$conn->query("
UPDATE marks SET
obtained_marks=".($marks===NULL?'NULL':$marks).",
total_marks=$totalMarks,
semester='$semester',
status='$status'
WHERE student_id='$sid' AND subject='$subject' AND exam_type='$examType' AND class='$class'
");

}else{

$conn->query("
INSERT INTO marks
(student_id,teacher_user_id,class,semester,subject,exam_type,total_marks,obtained_marks,status)
VALUES('$sid','$teacher_id','$class','$semester','$subject','$examType','$totalMarks',".($marks===NULL?'NULL':$marks).",'$status')
");

}

}

echo "<script>alert('Saved');location.reload();</script>";
}
?>