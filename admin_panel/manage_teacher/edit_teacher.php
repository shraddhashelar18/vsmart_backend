<?php
require_once("../auth.php");
require_once("../db.php");

if(!isset($_GET['id'])){
die("Teacher ID missing");
}

$user_id=$_GET['id'];

/* GET TEACHER */

$teacher=$conn->query("
SELECT *
FROM teachers
WHERE user_id='$user_id'
")->fetch_assoc();

$name=$teacher['full_name'];

/* GET EMAIL */

$user=$conn->query("
SELECT email
FROM users
WHERE user_id='$user_id'
")->fetch_assoc();

$email=$user['email'] ?? "";

/* GET ASSIGNED DATA */

$assigned = [];
$assignedClasses = [];
$departments = [];

$res=$conn->query("
SELECT class,subject
FROM teacher_assignments
WHERE user_id='$user_id'
AND status='active'
");

while($row=$res->fetch_assoc()){

$class = $row['class'];

$assignedClasses[$class] = true;

$baseClass = substr($class,0,4);

$assigned[$baseClass][]=$row['subject'];

/* detect department */

$departments[substr($class,0,2)] = true;

}
/* ALL DEPARTMENTS */

$allDepartments=['IF','CO','EJ'];

/* GET ALL CLASSES */

$classes=$conn->query("
SELECT class_name
FROM classes
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
?>

<!DOCTYPE html>
<html>
<head>

<title>Edit Teacher</title>

<style>

body{
font-family:Segoe UI;
background:#f4f6f9;
margin:0;
}

.topbar{
background:#009846;
color:white;
padding:18px 25px;
font-size:22px;
}

.wrapper{
max-width:900px;
margin:30px auto;
}

.input{
background:#eee;
padding:14px;
border-radius:10px;
margin-top:6px;
margin-bottom:18px;
border:none;
width:100%;
}

.back{
color:white;
text-decoration:none;
font-size:26px;
}


.chip{
display:inline-block;
padding:10px 18px;
border-radius:20px;
border:1px solid #ccc;
margin:6px;
cursor:pointer;
background:white;
}

.chip.selected{
background:#dcd3ff;
border-color:#b8a8ff;
}

.chip.selected::before{
content:"✔ ";
}

.chip input{
display:none;
}

.subject-section{
display:none;
margin-top:15px;
}

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

</style>

</head>

<body>

<div class="topbar">
<a href="manage_teachers.php" class="back">←</a>
Edit Teacher
</div>

<div class="wrapper">

<label>Teacher Name</label>
<input class="input" value="<?= $name ?>" readonly>

<label>Email</label>
<input class="input" value="<?= $email ?>" readonly>

<label>Departments</label>
<br>

<?php foreach($allDepartments as $dept): ?>

<label class="chip dept-chip <?= isset($departments[$dept])?'selected':'' ?>" 
onclick="toggleDepartment(this,'<?= $dept ?>')">

<input type="checkbox" name="departments[]" value="<?= $dept ?>" 
<?= isset($departments[$dept])?'checked':'' ?>>

<?= $dept ?>

</label>

<?php endforeach; ?>


<h4>Assign Classes</h4>

<?php
$departmentsList = ['IF','CO','EJ'];

foreach($departmentsList as $dept):

echo "<h4 id='heading-$dept'>".$dept." Department</h4>";

$cls=$conn->query("
SELECT class_name
FROM classes
WHERE department='$dept'
");

while($row=$cls->fetch_assoc()):

$class=$row['class_name'];
$isSelected=isset($assignedClasses[$class]);
?>

<label class="chip class-chip dept-<?= $dept ?> <?= $isSelected?'selected':'' ?>"
onclick="selectClass(this,'<?= $class ?>')">

<input type="checkbox" name="classes[]" value="<?= $class ?>" <?= $isSelected?'checked':'' ?>>

<?= $class ?>

</label>

<?php endwhile; ?>

<?php endforeach; ?>

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
}, $assigned[$class] ?? [])
);

?>

<label class="chip <?= $isAssigned ? 'selected' : '' ?>">

<input
type="checkbox"
name="subjects[<?= $class ?>][]"
value="<?= $sub ?>"
<?= $isAssigned ? 'checked' : '' ?>>

<?= $sub ?>

</label>

<?php endforeach; ?>

</div>

<?php endforeach; ?>
<button class="save">
Update Teacher
</button>

</div>
<script>

document.querySelectorAll(".chip").forEach(function(chip){

chip.addEventListener("click",function(e){

/* ignore class chips (they use selectClass) */

if(chip.classList.contains("class-chip")) return;

let checkbox = chip.querySelector("input");

if(!checkbox) return;

if(checkbox.disabled) return;

checkbox.checked = !checkbox.checked;

if(checkbox.checked){
chip.classList.add("selected");
}else{
chip.classList.remove("selected");
}

});

});

</script>
<script>

/* DEPARTMENT TOGGLE */

function toggleDepartment(element, dept){

let checkbox = element.querySelector("input");

/* toggle checkbox */

checkbox.checked = !checkbox.checked;

/* toggle UI */

if(checkbox.checked){
element.classList.add("selected");
}else{
element.classList.remove("selected");
}

/* show/hide class sections */

document.querySelectorAll(".dept-"+dept).forEach(cls=>{
cls.style.display = checkbox.checked ? "inline-block" : "none";
});

/* show/hide department heading */

let heading = document.getElementById("heading-"+dept);

if(heading){
heading.style.display = checkbox.checked ? "block" : "none";
}

}

/* CLASS SELECT */

function selectClass(element,className){

let checkbox = element.querySelector("input");

/* toggle checkbox */

checkbox.checked = !checkbox.checked;

if(checkbox.checked){
element.classList.add("selected");
}else{
element.classList.remove("selected");
}

/* IF6KA → IF6K */

let baseClass = className.substring(0,4);

let section = document.getElementById("subjects_"+baseClass);

if(section){

/* toggle subjects instead of hiding others */

if(checkbox.checked){
section.style.display="block";
}else{
section.style.display="none";
}

}

}

document.addEventListener("DOMContentLoaded", function(){

document.querySelectorAll(".class-chip input:checked").forEach(function(ch){

let baseClass = ch.value.substring(0,4);

let section = document.getElementById("subjects_"+baseClass);

if(section){
section.style.display="block";
}

});

});

document.querySelectorAll(".class-chip.selected").forEach(function(el){

let className = el.querySelector("input").value;
let baseClass = className.substring(0,4);

let section = document.getElementById("subjects_"+baseClass);

if(section){
section.style.display="block";
}

});

</script>

</body>
</html>