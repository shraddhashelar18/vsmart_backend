<?php
require_once("../auth.php");
require_once("../db.php");

if(!isset($_GET['class'])){
    echo "Class not selected";
    exit;
}

$class=$_GET['class'];
$error="";

if(isset($_POST['save'])){

$name=trim($_POST['name']);
$email=trim($_POST['email']);
$password=trim($_POST['password']);
$mobile=trim($_POST['mobile']);
$parent_mobile=trim($_POST['parent_mobile']);
$roll=trim($_POST['roll']);
$enrollment=trim($_POST['enrollment']);

if($name==""){
$error="Full name required";
}

elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)){
$error="Invalid email";
}

elseif(strlen($password)<6){
$error="Password must be minimum 6 characters";
}

elseif(!preg_match('/^[0-9]{10}$/',$mobile)){
$error="Mobile must be 10 digits";
}

elseif(!preg_match('/^[0-9]{10}$/',$parent_mobile)){
$error="Parent mobile must be 10 digits";
}

else{

$check=$conn->prepare("SELECT user_id FROM users WHERE email=?");
$check->bind_param("s",$email);
$check->execute();
$check->store_result();

if($check->num_rows>0){
$error="Email already exists";
}

else{

$conn->begin_transaction();

$hash=password_hash($password,PASSWORD_BCRYPT);

$user=$conn->prepare("
INSERT INTO users(email,password,role,status)
VALUES(?,?, 'student','approved')
");

$user->bind_param("ss",$email,$hash);
$user->execute();

$user_id=$conn->insert_id;

$student=$conn->prepare("
INSERT INTO students(user_id,full_name,mobile_no,parent_mobile_no,roll_no,enrollment_no,class)
VALUES(?,?,?,?,?,?,?)
");

$student->bind_param("issssss",$user_id,$name,$mobile,$parent_mobile,$roll,$enrollment,$class);
$student->execute();

$conn->commit();

header("Location:manage_students.php?class=".$class);
exit;

}

}

}
?>

<!DOCTYPE html>
<html>

<head>

<link rel="stylesheet" href="/vsmart/admin_panel/assets/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>

.form-box{
width:520px;
margin:auto;
margin-top:40px;
background:white;
padding:40px;
border-radius:20px;
box-shadow:0 8px 20px rgba(0,0,0,0.08);
}

.input-box{
display:flex;
align-items:center;
border:1px solid #ddd;
border-radius:14px;
padding:14px;
margin-bottom:18px;
}

.input-box i{
margin-right:10px;
}

.input-box input{
border:none;
outline:none;
width:100%;
}

.btn{
width:100%;
background:#0A8F3E;
color:white;
padding:14px;
border:none;
border-radius:25px;
font-size:16px;
cursor:pointer;
}

.readonly{
background:#f3f3f3;
}

</style>

</head>

<body>

<div class="header">
<h1>Add Student</h1>
</div>

<div class="form-box">

<?php if($error!=""){ ?>
<p style="color:red;text-align:center"><?php echo $error; ?></p>
<?php } ?>

<form method="POST">

<div class="input-box">
<i class="material-icons">person</i>
<input type="text" name="name" placeholder="Full Name">
</div>

<div class="input-box">
<i class="material-icons">email</i>
<input type="text" name="email" placeholder="Email">
</div>

<div class="input-box">
<i class="material-icons">lock</i>
<input type="password" name="password" placeholder="Password">
</div>

<div class="input-box">
<i class="material-icons">phone</i>
<input type="text" name="mobile" placeholder="Mobile">
</div>

<div class="input-box">
<i class="material-icons">phone</i>
<input type="text" name="parent_mobile" placeholder="Parent Mobile">
</div>

<div class="input-box">
<i class="material-icons">badge</i>
<input type="text" name="roll" placeholder="Roll No">
</div>

<div class="input-box">
<i class="material-icons">tag</i>
<input type="text" name="enrollment" placeholder="Enrollment">
</div>

<div class="input-box readonly">
<i class="material-icons">school</i>
<input type="text" value="<?php echo $class; ?>" readonly>
</div>

<button class="btn" name="save">Save Student</button>

</form>

</div>

</body>
</html>