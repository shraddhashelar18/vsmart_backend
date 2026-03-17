<?php
require_once("../auth.php");
require_once("../db.php");

if(!isset($_GET['department'])){
die("Department missing");
}

$department=$_GET['department'];

/* GET CLASSES OF DEPARTMENT */

$classes=$conn->query("
SELECT class_name
FROM classes
WHERE department='$department'
");

/* GET SUBJECTS FROM semester_subjects */

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
$baseClass = substr($row['class'],0,4);  // IF2KA → IF2K

$assigned[$baseClass][]=$row['subject'];
}

?>

<!DOCTYPE html>
<html>
<head>

<title>Add Teacher</title>

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
background:#eee;
color:#888;
cursor:not-allowed;
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
margin-right:10px;
}

</style>

</head>

<body>

<div class="topbar">

<a href="manage_teachers.php?department=<?= $department ?>" class="back">
←
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
?>

<label class="chip class-chip" onclick="selectClass(this,'<?= $class ?>')">

<input
type="radio"
name="class"
value="<?= $class ?>"
style="display:none">

<?= $class ?>

</label>

<?php endwhile; ?>

<h4>Subjects</h4>

<?php foreach($subjects as $class=>$subs): ?>

<div id="subjects_<?= $class ?>" class="subject-section">

<b><?= $class ?></b>

<br>

<?php foreach($subs as $sub):

$isAssigned = in_array($sub,$assigned[$class] ?? []);

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

/* CLASS CLICK */

function selectClass(element,className){

document.querySelectorAll(".class-chip").forEach(c=>{
c.classList.remove("selected");
});

element.classList.add("selected");

element.querySelector("input").checked = true;

document.querySelectorAll(".subject-section").forEach(sec=>{
sec.style.display="none";
});

/* convert IF2KA → IF2K */

let baseClass = className.substring(0,4);

let section = document.getElementById("subjects_" + baseClass);

if(section){
section.style.display = "block";
}

}

/* FORM VALIDATION */

document.getElementById("teacherForm").onsubmit=function(){

let classSelected=document.querySelector("input[name='class']:checked");

if(!classSelected){
alert("Please select a class");
return false;
}

return true;

}

</script>
<script>

document.querySelectorAll(".chip").forEach(chip=>{

chip.addEventListener("click",function(){

let checkbox = this.querySelector("input");

if(checkbox.disabled) return;

checkbox.checked = !checkbox.checked;

this.classList.toggle("selected");

});

});

</script>
<script>

document.querySelectorAll(".chip").forEach(function(chip){

chip.addEventListener("click",function(){

let checkbox = chip.querySelector("input");

if(checkbox.disabled) return;

/* toggle checkbox */

checkbox.checked = !checkbox.checked;

/* toggle selected design */

if(checkbox.checked){
chip.classList.add("selected");
}else{
chip.classList.remove("selected");
}

});

});

</script>
</body>
</html>