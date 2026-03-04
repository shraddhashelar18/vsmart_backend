<?php
require_once "db.php";
session_start();

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "All fields are required";
    } else {

        $stmt = $conn->prepare("
            SELECT user_id, password, role, status
            FROM users
            WHERE email=?
            LIMIT 1
        ");
        $stmt->bind_param("s",$email);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows==1){
            $user=$result->fetch_assoc();

            if($user['role']!='admin'){
                $error="Access denied.";
            }
            elseif($user['status']!='approved'){
                $error="Account not approved.";
            }
            elseif(!password_verify($password,$user['password'])){
                $error="Incorrect password.";
            }
            else{
                $_SESSION['admin_id']=$user['user_id'];
                header("Location: dashboard.php");
                exit;
            }
        } else {
            $error="Account not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Login - VSmart</title>
<style>

body{
    margin:0;
    font-family:Segoe UI, sans-serif;
    background:#f4f6f9;
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
}

/* Full Center Box */
.login-container{
    width:100%;
    max-width:420px;
    text-align:center;
}

/* Logo Circle */
.logo-circle{
    width:90px;
    height:90px;
    background:#0A8F3E;
    border-radius:50%;
    margin:auto;
    display:flex;
    align-items:center;
    justify-content:center;
    color:white;
    font-size:40px;
}

/* App Name */
.app-name{
    font-size:28px;
    font-weight:600;
    margin:15px 0 30px;
    color:#0A8F3E;
}

/* Input Field */
.input-box{
    width:100%;
    padding:15px;
    margin-bottom:15px;
    border:none;
    border-radius:12px;
    background:#e9ecef;
    font-size:14px;
}

/* Login Button */
.login-btn{
    width:100%;
    padding:15px;
    background:#0A8F3E;
    color:white;
    border:none;
    border-radius:25px;
    font-size:16px;
    cursor:pointer;
    transition:0.3s;
}

.login-btn:hover{
    background:#087a35;
}

.error{
    color:red;
    font-size:13px;
    margin-bottom:10px;
}

</style>
</head>

<body>

<div class="login-container">

    <div class="logo-circle">
        🎓
    </div>

    <div class="app-name">VSmart</div>

    <form method="POST">

        <input type="email" 
               name="email" 
               class="input-box"
               placeholder="Enter your email"
               required>

        <input type="password" 
               name="password" 
               class="input-box"
               placeholder="Enter your password"
               required>

        <?php if($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <button class="login-btn">Login</button>

    </form>

</div>

</body>
</html>