<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once("../config.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'] ?? "";
    $password = $_POST['password'] ?? "";

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password']) || $password == $user['password']) {

            $role = strtolower(trim($user['role']));

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $role;

            if ($role == "admin") {
                $_SESSION['admin_id'] = $user['user_id'];
                $_SESSION['admin_name'] = $user['full_name'];
                header("Location: ../admin_panel/dashboard.php");
                exit;
            }
            elseif ($role == "hod") {
                header("Location: ../hod_panel/dashboard.php");
                exit;
            }
            elseif ($role == "teacher") {

                $t = $conn->prepare("SELECT * FROM teachers WHERE user_id=? LIMIT 1");
                $t->bind_param("i", $user['user_id']);
                $t->execute();
                $res = $t->get_result();

                if($res->num_rows > 0){
                    $teacher = $res->fetch_assoc();

                    $_SESSION['teacher_id'] = $user['user_id'];
                    $_SESSION['teacher_name'] = $teacher['full_name'];

                    header("Location: ../teacher_panel/teacher_dashboard.php");
                    exit;
                } else {
                    $message = "Teacher not found";
                }
            }
            elseif ($role == "student") {
                header("Location: student_dashboard.php");
                exit;
            } 
            elseif ($user['role'] == "principal") {
                header("Location: ../principal_panel/dashboard.php");
            } 
            elseif ($user['role'] == "parent") {
                header("Location: parent_dashboard.php");
            }

            exit;

        } else {
            $message = "Invalid password";
        }

    } else {
        $message = "User not found";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Vsmart Login</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
body{
    margin:0;
    font-family: Arial, sans-serif;
    background:#f5f5f5;

    display:flex;
    justify-content:center;
    align-items:center;

    height:100vh;
}

.login-container{
    width:100%;
    max-width:350px;
    text-align:center;
}
<<<<<<< HEAD
.login-logo{
    width:120px;
    height:auto;          /* 🔥 IMPORTANT */
    display:block;
    margin:0 auto 15px;
}
.logo-circle{
    width:70px;
    height:70px;
    background:#009846;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    margin:auto;
=======

/* 🔥 LOGO FIXED */
.logo{
    margin-bottom:15px;
    text-align:center;
>>>>>>> b623440530b43e32f79a9ee94002dd32e6d3fc78
}

.logo img{
    width:120px;
    height:auto;
    display:block;
    margin:0 auto;
    object-fit:contain;
}

/* Title */
.logo-title{
    color:#009846;
    font-size:22px;
    margin-bottom:15px;
    font-weight:600;
}

.input-box{
    position:relative;
    margin-top:15px;
}

.input-box input{
    width:75%;
    padding:14px 40px;
    border:none;
    background:#eee;
    border-radius:10px;
    outline:none;
    font-size:14px;
}

.input-box i{
    position:absolute;
    left:12px;
    top:15px;
    color:#666;
}

.toggle{
    position:absolute;
    right:45px;
    top:15px;
    cursor:pointer;
    color:#666;
}

.login-btn{
    margin-top:20px;
    width:100%;
    padding:14px;
    background:#009846;
    color:white;
    border:none;
    border-radius:10px;
    font-size:16px;
    cursor:pointer;
}

.login-btn:hover{
    background:#007a38;
}

.error{
    color:red;
    margin-top:10px;
}
</style>
</head>

<body>

<div class="login-container">

<<<<<<< HEAD
<img src="../assets/logo.png" class="login-logo">
=======
<!-- ✅ LOGO WITH FALLBACK -->
<div class="logo">
    <img src="../web_panel/assets/logo.png"
         onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/3/3b/Graduation_cap_icon.svg'"
         alt="Vsmart Logo">
</div>
>>>>>>> b623440530b43e32f79a9ee94002dd32e6d3fc78

<div class="logo-title">Vsmart</div>

<form method="POST">

<div class="input-box">
<i class="fa fa-envelope"></i>
<input type="email" name="email" placeholder="Enter your email" required>
</div>

<div class="input-box">
<i class="fa fa-lock"></i>
<input type="password" id="password" name="password" placeholder="Enter your password" required>
<span class="toggle" onclick="togglePassword()">
<i class="fa fa-eye-slash"></i>
</span>
</div>

<button class="login-btn">Login</button>

<?php if($message != ""){ ?>
<div class="error"><?php echo $message; ?></div>
<?php } ?>

</form>

</div>

<script>
function togglePassword(){
    let pass = document.getElementById("password");

    if(pass.type === "password"){
        pass.type = "text";
    } else {
        pass.type = "password";
    }
}
</script>

</body>
</html>