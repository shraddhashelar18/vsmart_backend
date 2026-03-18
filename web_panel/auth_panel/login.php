<?php
session_start();
require_once("../config.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'] ?? "";
    $password = $_POST['password'] ?? "";

    // 🔐 Check user from database
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $user = $result->fetch_assoc();

        // ✅ If password is hashed use password_verify
        if (password_verify($password, $user['password']) || $password == $user['password']) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            // ✅ Redirect based on role
            if ($user['role'] == "admin") {
                header("Location: ../admin_panel/dashboard.php");
            } elseif ($user['role'] == "hod") {
                header("Location: ../hod_panel/dashboard.php");
            } elseif ($user['role'] == "teacher") {
                header("Location: teacher_dashboard.php");
            } elseif ($user['role'] == "student") {
                header("Location: student_dashboard.php");
            } elseif ($user['role'] == "principal") {
                header("Location: principal_dashboard.php");
            } elseif ($user['role'] == "parent") {
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
    font-family: Arial, sans-serif;
    background:#f5f5f5;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
    margin:0;
}
.login-container{
    width:350px;
    text-align:center;
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
}
.logo-circle i{
    color:white;
    font-size:28px;
}
.logo-title{
    color:#009846;
    font-size:22px;
    margin-top:10px;
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

<div class="logo-circle">
<i class="fa fa-graduation-cap"></i>
</div>

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