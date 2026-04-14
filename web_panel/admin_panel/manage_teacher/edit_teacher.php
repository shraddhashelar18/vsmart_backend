<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../../config.php");

$user_id = $_GET['id'];
$department = $_GET['department'] ?? '';

/* TEACHER */
$teacher = $conn->query("SELECT * FROM teachers WHERE user_id='$user_id'")->fetch_assoc();
$name = $teacher['full_name'];

$phone = $teacher['mobile_no'] ?? '';
$emp_id = $teacher['employee_id'] ?? '';

/* EMAIL */
$user = $conn->query("SELECT email FROM users WHERE user_id='$user_id'")->fetch_assoc();
$email = $user['email'] ?? "";

/* ASSIGNED (THIS TEACHER) */
$assigned = [];
$assignedClasses = [];
$departments = [];

$res = $conn->query("
SELECT class,subject FROM teacher_assignments
WHERE user_id='$user_id' AND status='active'
");

while($row=$res->fetch_assoc()){
    $class = $row['class'];

    $assignedClasses[$class] = true;
    $assigned[$class][] = strtolower(trim($row['subject']));
    $departments[substr($class,0,2)] = true;
}

/* OTHER TEACHERS (DISABLE) */
$otherAssigned = [];

$res = $conn->query("
SELECT class, subject 
FROM teacher_assignments 
WHERE user_id != '$user_id' AND status='active'
");

while($row = $res->fetch_assoc()){
    $class = $row['class'];
    $otherAssigned[$class][] = strtolower(trim($row['subject']));
}

/* SUBJECTS */
$subjects = [];
$res = $conn->query("SELECT class,subject_name FROM semester_subjects");

while($row = $res->fetch_assoc()){
    $key = substr($row['class'], 0, 4); // FIX
    $subjects[$key][] = $row['subject_name'];
}


$allDepartments = ['IF','CO','EJ'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Teacher</title>

<style>
body{font-family:Segoe UI;background:#f4f6f9;margin:0;}
.topbar{background:#009846;color:white;padding:18px;font-size:20px;}
.wrapper{max-width:900px;margin:30px auto;}

.input{
background:#eee;
padding:12px;
border-radius:8px;
margin-bottom:15px;
border:none;
width:100%;
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

/* GREY NON-EDITABLE INPUT */
.readonly{
background:#e0e0e0 !important;
color:#888;
cursor:not-allowed;
}

/* COUNT TEXT (10/10, 6/6) */
.count{
position:absolute;
right:10px;
top:50%;
transform:translateY(-50%);
font-size:12px;
color:#888;
}

.chip.selected{
background:#e6dcff;
border-color:#c3b5ff;
color:#5a4fcf;
}

.chip.disabled {
    background: #e0e0e0;
    color: #888;
    cursor: not-allowed;
    pointer-events: none;
}

.chip input{display:none;}

.subject-section{
display:none;
margin-top:20px;
padding-top:10px;
border-top:1px solid #ddd;
}

.save{
width:100%;
padding:14px;
background:#009846;
color:white;
border:none;
border-radius:10px;
font-size:16px;
margin-top:20px;
}
</style>
</head>

<body>

<div class="topbar">
<a href="manage_teachers.php?department=<?= $department ?>" style="color:white;text-decoration:none;">←</a>
Edit Teacher
</div>

<div class="wrapper">

<form method="POST" action="update_teacher.php">

<input type="hidden" name="user_id" value="<?= $user_id ?>">
<input type="hidden" name="department" value="<?= $department ?>">

<!-- NAME (EDITABLE) -->
<label>Teacher Name</label>
<input class="input" name="full_name" value="<?= $name ?>" required>

<!-- EMAIL (READONLY) -->
<label>Email</label>
<input class="input readonly" value="<?= $email ?>" readonly>

<!-- PHONE (READONLY) -->
<label>Phone</label>
<div style="position:relative;">
<input class="input" id="phoneInput" name="mobile_no" value="<?= $phone ?>" maxlength="10" required>
<span class="count" id="phoneCount"><?= strlen($phone) ?>/10</span>
</div>

<!-- EMPLOYEE ID (READONLY) -->

<label>Employee ID</label>
<div style="position:relative;">
<input class="input readonly" id="empInput" value="<?= $emp_id ?>" readonly>
<span class="count" id="empCount"><?= strlen($emp_id) ?>/6</span>
</div>

<!-- DEPARTMENTS -->
<label>Departments</label><br>
<?php foreach($allDepartments as $dept): ?>
<div class="chip dept-chip <?= isset($departments[$dept])?'selected':'' ?>">
<input type="checkbox" name="departments[]" value="<?= $dept ?>" <?= isset($departments[$dept])?'checked':'' ?>>
<?= $dept ?>
</div>
<?php endforeach; ?>

<h3>Assign Classes</h3>

<?php
foreach($allDepartments as $dept):

echo "<div class='dept-section' id='dept_$dept'>";   // ✅ START WRAP

echo "<h4>".$dept." Department</h4>";

$cls=$conn->query("SELECT class_name FROM classes WHERE department='$dept'");

while($row=$cls->fetch_assoc()):
$class=$row['class_name'];
$isSelected=isset($assignedClasses[$class]);
?>

<div class="chip class-chip <?= $isSelected?'selected':'' ?>">
<input type="checkbox" name="classes[]" value="<?= $class ?>" <?= $isSelected?'checked':'' ?>>
<?= $class ?>
</div>

<?php endwhile;

echo "</div>";   // ✅ END WRAP

endforeach;
?>
<h3>Subjects</h3>

<?php
foreach($allDepartments as $dept):

$cls=$conn->query("SELECT class_name FROM classes WHERE department='$dept'");

while($row=$cls->fetch_assoc()):
$class = $row['class_name'];
?>

<div id="subjects_<?= $class ?>" class="subject-section">

<b><?= $class ?></b><br>

<?php 
$baseClass = substr($class, 0, 4);

foreach($subjects[$baseClass] ?? [] as $sub):

    $cleanSub = strtolower(trim($sub));

    $isAssigned = in_array(
        $cleanSub,
        array_map(fn($s)=>strtolower(trim($s)), $assigned[$class] ?? [])
    );

    $isDisabled = in_array(
        $cleanSub,
        $otherAssigned[$class] ?? []
    );
?>

<div class="chip subject-chip 
<?= $isAssigned ? 'selected' : '' ?> 
<?= $isDisabled ? 'disabled' : '' ?>">

    <input type="checkbox"
        name="subjects[<?= $class ?>][]"
        value="<?= $sub ?>"
        <?= $isAssigned ? 'checked' : '' ?>
        <?= $isDisabled ? 'disabled' : '' ?>
    >

    <?= $sub ?>

</div>

<?php endforeach; ?>
</div>

<?php endwhile; endforeach; ?>

<button class="save">Update Teacher</button>

</form>

</div>

<script>

/* CLASS CLICK */
document.querySelectorAll(".class-chip").forEach(chip=>{
chip.onclick = function(){

let cb = chip.querySelector("input");
cb.checked = !cb.checked;

let className = cb.value;
let section = document.getElementById("subjects_"+className);

if(cb.checked){

chip.classList.add("selected");
if(section) section.style.display = "block";

}else{

chip.classList.remove("selected");

if(section){
section.style.display = "none";

/* clear subjects */
section.querySelectorAll("input").forEach(s=>{
if(!s.disabled){
s.checked = false;
s.closest(".chip").classList.remove("selected");
}
});
}

}

};
});

/* SUBJECT CLICK */
document.addEventListener("click", function(e){

let chip = e.target.closest(".subject-section .chip");

if(!chip || chip.classList.contains("disabled")) return;

let cb = chip.querySelector("input");

cb.checked = !cb.checked;

if(cb.checked){
chip.classList.add("selected");
}else{
chip.classList.remove("selected");
}

});

/* DEPARTMENT CLICK */
document.querySelectorAll(".dept-chip").forEach(chip=>{
chip.onclick = function(){

let cb = chip.querySelector("input");
cb.checked = !cb.checked;

chip.classList.toggle("selected");

};
});

/* PRELOAD */
window.onload = function(){

// INITIAL HIDE BASED ON SELECTED DEPT
document.querySelectorAll(".dept-chip input").forEach(cb=>{
    let dept = cb.value;
    let section = document.getElementById("dept_" + dept);

    if(!cb.checked){
        section.style.display = "none";
    }
});

document.querySelectorAll(".class-chip input:checked").forEach(cb=>{

let section = document.getElementById("subjects_"+cb.value);
if(section) section.style.display = "block";

});

};

// DEPARTMENT SHOW/HIDE CLASSES
document.querySelectorAll(".dept-chip").forEach(chip=>{
chip.onclick = function(){

    let cb = chip.querySelector("input");
    cb.checked = !cb.checked;

    chip.classList.toggle("selected");

    let dept = cb.value;
    let section = document.getElementById("dept_" + dept);

    if(cb.checked){
        section.style.display = "block";
    } else {
        section.style.display = "none";

        // UNCHECK classes
        section.querySelectorAll(".class-chip input").forEach(c=>{
            c.checked = false;
            c.closest(".chip").classList.remove("selected");
        });

        // HIDE subjects
        section.querySelectorAll(".subject-section").forEach(s=>{
            s.style.display = "none";
        });
    }

};
});

</script>
<script>

/* FORM VALIDATION */
document.querySelector("form").addEventListener("submit", function(e){

    let classChecked = document.querySelectorAll(".class-chip input:checked");
    
    if(classChecked.length === 0){
        e.preventDefault();

        alert("Please assign at least one class and subject.\nOtherwise teacher will not appear.");
        return;
    }

    let hasValidSubject = false;

    classChecked.forEach(cb=>{
        let className = cb.value;
        let section = document.getElementById("subjects_" + className);

        if(section){
            let subjects = section.querySelectorAll("input:checked:not(:disabled)");
            if(subjects.length > 0){
                hasValidSubject = true;
            }
        }
    });

    if(!hasValidSubject){
        e.preventDefault();

        alert("Each selected class must have at least one subject.\nOtherwise teacher will not appear.");
        return;
    }

});
</script>
<script>
// PHONE LIVE COUNT
const phoneInput = document.getElementById("phoneInput");
const phoneCount = document.getElementById("phoneCount");

phoneInput.addEventListener("input", function(){
    phoneCount.innerText = this.value.length + "/10";
});
</script>
</body>
</html>