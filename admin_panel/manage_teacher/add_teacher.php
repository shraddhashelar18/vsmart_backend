<?php
require_once("../auth.php");
require_once("../db.php");

if(!isset($_GET['department'])){
die("Department missing");
}

$department=$_GET['department'];

/* GET CLASSES */

$classes=$conn->query("
SELECT class_name
FROM classes
WHERE department='$department'
");

/* GET SUBJECTS */

$subjects=[];

$res=$conn->query("
SELECT class,subject_name
FROM semester_subjects
");

while($row=$res->fetch_assoc()){
$subjects[$row['class']][]=$row['subject_name'];
}

/* GET ASSIGNED SUBJECTS */

$assigned=[];

$res=$conn->query("
SELECT class,subject
FROM teacher_assignments
WHERE status='active'
");

while($row=$res->fetch_assoc()){
$baseClass = substr($row['class'],0,4);
$assigned[$baseClass][]=$row['subject'];
}
?>

<!DOCTYPE html>
<html>

<head>

<title>Add Teacher</title>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>

body{
font-family:Segoe UI;
background:#f4f6f9;
margin:0;
}

/* TOPBAR */

.topbar{
background:#009846;
color:white;
padding:18px 25px;
font-size:22px;
display:flex;
align-items:center;
gap:10px;
}

/* WRAPPER */

.wrapper{
max-width:900px;
margin:30px auto;
}

/* INPUT */

.input{
background:#eee;
padding:14px;
border-radius:10px;
margin-top:6px;
margin-bottom:18px;
border:none;
width:100%;
}

/* CHIP */

.chip{
display:inline-block;
padding:10px 18px;
border-radius:20px;
border:1px solid #ccc;
margin:6px;
cursor:pointer;
background:white;
transition:0.2s;
}

.chip:hover{
background:#f1f1f1;
}

.chip.selected{
background:#dcd3ff;
border-color:#b8a8ff;
}

.chip.disabled{
background:#e0e0e0;
color:#888;
border-color:#ccc;
cursor:not-allowed;
pointer-events:none;
}

.chip input{
display:none;
}

/* SUBJECT SECTION */

.subject-section{
display:none;
margin-top:15px;
}

/* SAVE BUTTON */

.save{
width:100%;
padding:14px;
background:#009846;
color:white;
border:none;
border-radius:12px;
font-size:18px;
margin-top:25px;
cursor:pointer;
}

.back{
color:white;
text-decoration:none;
font-size:22px;
}

</style>

</head>

<body>

<div class="topbar">

<a href="manage_teachers.php?department=<?= $department ?>" class="back">
<span class="material-icons">arrow_back</span>
</a>

Add Teacher

</div>

<div class="wrapper">

<form method="POST" action="save_teacher.php" id="teacherForm">

<label>Teacher Name</label>

<input
class="input"
name="name"
placeholder="Enter full name"
required
pattern="[A-Za-z ]+">

<label>Email</label>

<input
class="input"
type="email"
name="email"
placeholder="teacher@email.com"
required>

<label>Password</label>

<input
class="input"
type="password"
name="password"
placeholder="Enter password"
required
minlength="6">

<label>Departments</label>

<br>

<div class="chip selected">
✔ <?= $department ?>
</div>

<input type="hidden" name="department" value="<?= $department ?>">

<h4>Assign Classes</h4>

<?php while($row=$classes->fetch_assoc()): 

$class=$row['class_name'];
$baseClass=substr($class,0,4);

/* TOTAL SUBJECTS */

$totalSubjects=count($subjects[$baseClass] ?? []);

/* ASSIGNED SUBJECTS */

$assignedSubjects=count($assigned[$baseClass] ?? []);

/* DISABLE IF ALL ASSIGNED */

$isDisabled=($totalSubjects>0 && $totalSubjects==$assignedSubjects);

?>

<label class="chip class-chip <?= $isDisabled ? 'disabled' : '' ?>"
onclick="<?= $isDisabled ? '' : "selectClass(this,'$class')" ?>">

<input
type="checkbox"
name="classes[]"
value="<?= $class ?>"
<?= $isDisabled ? 'disabled' : '' ?>>

<?= $class ?>

</label>

<?php endwhile; ?>

<h4>Subjects</h4>

<?php foreach($subjects as $class=>$subs): ?>

<div id="subjects_<?= $class ?>" class="subject-section">

<b><?= $class ?></b>

<br>

<?php foreach($subs as $sub):

$isAssigned = in_array(
strtolower(trim($sub)),
array_map(function($s){
    return strtolower(trim($s));
}, $assigned[substr($class,0,4)] ?? [])
);

?>

<label class="chip <?= $isAssigned ? 'disabled selected' : '' ?>">

<input
type="checkbox"
name="subjects[<?= $class ?>][]"
value="<?= $sub ?>"
<?= $isAssigned ? 'checked disabled' : '' ?>>

<?= $sub ?>

</label>

<?php endforeach; ?>

</div>

<?php endforeach; ?>

<button class="save">
Save Teacher
</button>

</form>

</div>

<script>

/* CLASS SELECT */

function selectClass(element,className){

if(element.classList.contains("disabled")) return;

let checkbox = element.querySelector("input");

checkbox.checked = !checkbox.checked;

/* TOGGLE PURPLE UI */

if(checkbox.checked){
element.classList.add("selected");
}else{
element.classList.remove("selected");
}

/* SHOW SUBJECT SECTION */

let baseClass = className.substring(0,4);

let section = document.getElementById("subjects_"+baseClass);

if(section){

if(checkbox.checked){
section.style.display="block";
}else{
section.style.display="none";
}

}

}


/* SUBJECT CHIP CLICK */

document.querySelectorAll(".subject-section .chip").forEach(function(chip){

chip.addEventListener("click",function(){

let checkbox = chip.querySelector("input");

if(!checkbox) return;

if(checkbox.disabled) return;

/* TOGGLE CHECK */

checkbox.checked = !checkbox.checked;

/* TOGGLE PURPLE */

if(checkbox.checked){
chip.classList.add("selected");
}else{
chip.classList.remove("selected");
}

});

});


/* FORM VALIDATION */

document.getElementById("teacherForm").onsubmit=function(){

let classSelected=document.querySelector("input[name='classes[]']:checked");

if(!classSelected){
alert("Please select at least one class");
return false;
}

return true;

}

</script>

</body>
</html>