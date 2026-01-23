<?php
session_start();
require_once "db.php";

/* Redirect if already logged in */
if (!empty($_SESSION['user_id']) && !empty($_SESSION['role'])) {

    switch ($_SESSION['role']) {

        case 'admin':
            header("Location: admin/dashboard.php");
            break;

        case 'student':
            header("Location: student/dashboard.php");
            break;

        case 'teacher':
            header("Location: teacher/dashboard.php");
            break;

        case 'parent':
            header("Location: parent/dashboard.php");
            break;

        case 'hod':
            header("Location: hod/dashboard.php");
            break;

        default:
            session_destroy();
            header("Location: login.php");
    }
    exit;
}


/* ===============================
   Handle Login
================================ */
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = "All fields are required";
    } else {

        $stmt = mysqli_prepare(
            $conn,
            "SELECT user_id, email, password, role, status 
             FROM users 
             WHERE email = ? 
             LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user   = mysqli_fetch_assoc($result);

        if (!$user) {
            $error = "Invalid email or password";
        } elseif (!password_verify($password, $user['password'])) {
            $error = "Invalid email or password";
        } elseif ($user['status'] !== 'approved') {
            $error = "Your account is pending admin approval";
        } else {

            // Login success
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role']    = $user['role'];

            switch ($user['role']) {
                case 'admin':
                    header("Location: admin/dashboard.php");
                    break;
                case 'student':
                    header("Location: student/dashboard.php");
                    break;
                case 'teacher':
                    header("Location: teacher/dashboard.php");
                    break;
                case 'parent':
                    header("Location: parent/dashboard.php");
                    break;
                    case 'principal':
    header("Location: principal/dashboard.php");
    break;

                case 'hod':
                    header("Location: hod/dashboard.php");
                    break;
                default:
                    session_destroy();
                    $error = "Invalid role assigned";
            }
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>

<h2>Login</h2>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<form method="post">
    <label>Email</label><br>
    <input type="email" name="email" required><br><br>

    <label>Password</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Login</button>
</form>

</body>
</html>
