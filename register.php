<?php
include("db.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = mysqli_real_escape_string($conn, $_POST['full_name'] ?? '');
    $email     = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $role      = $_POST['role'] ?? '';

    if ($full_name=="" || $email=="" || $password=="" || $role=="") {
        $message = "All fields are required";
    } else {

        $valid_roles = ['student','teacher','parent'];
        if (!in_array($role, $valid_roles)) {
            $message = "Invalid role selected";
        } else {

            $check = mysqli_query($conn,
                "SELECT user_id FROM users WHERE email='$email'"
            );

            if (mysqli_num_rows($check) > 0) {
                $message = "Email already registered";
            } else {

                $hashed = password_hash($password, PASSWORD_DEFAULT);

                mysqli_query($conn,
                    "INSERT INTO users (email,password,role,status)
                     VALUES ('$email','$hashed','$role','pending')"
                );

                $user_id = mysqli_insert_id($conn);

                // ---------- STUDENT ----------
                if ($role == "student") {

                    $roll_no = $_POST['roll_no'] ?? '';
                    $class   = $_POST['class'] ?? '';
                    $mobile  = $_POST['mobile_no'] ?? '';
                    $pmobile = $_POST['parent_mobile_no'] ?? '';

                    if ($roll_no=="" || $class=="" || $mobile=="" || $pmobile=="") {
                        $message = "All student fields are required";
                    } else {
                        mysqli_query($conn,
                            "INSERT INTO students
                             (roll_no,user_id,full_name,class,mobile_no,parent_mobile_no)
                             VALUES
                             ('$roll_no',$user_id,'$full_name','$class','$mobile','$pmobile')"
                        );
                        $message = "Student registered successfully. Waiting for approval";
                    }
                }

                // ---------- TEACHER ----------
                elseif ($role == "teacher") {

                    $emp_id = $_POST['employee_id'] ?? '';
                    $mobile = $_POST['mobile_no'] ?? '';

                    if ($emp_id=="" || $mobile=="") {
                        $message = "All teacher fields are required";
                    } else {
                        mysqli_query($conn,
                            "INSERT INTO teachers
                             (employee_id,user_id,full_name,mobile_no)
                             VALUES
                             ('$emp_id',$user_id,'$full_name','$mobile')"
                        );
                        $message = "Teacher registered successfully. Waiting for approval";
                    }
                }

                // ---------- PARENT ----------
                elseif ($role == "parent") {

                    $enroll = $_POST['enrollment_no'] ?? '';
                    $mobile = $_POST['mobile_no'] ?? '';

                    if ($enroll=="" || $mobile=="") {
                        $message = "All parent fields are required";
                    } else {
                        mysqli_query($conn,
                            "INSERT INTO parents
                             (enrollment_no,user_id,full_name,mobile_no)
                             VALUES
                             ('$enroll',$user_id,'$full_name','$mobile')"
                        );
                        $message = "Parent registered successfully. Waiting for approval";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Registration</title>
<style>
body{font-family:Arial;background:#f5f5f5;}
.box{width:420px;margin:40px auto;padding:25px;background:#fff;border-radius:8px;}
input,select{width:100%;padding:10px;margin-top:10px;}
button{margin-top:15px;width:100%;padding:10px;background:#009846;color:white;border:none;border-radius:5px;}
.msg{margin-top:15px;color:green;text-align:center;}
</style>
</head>

<body>
<div class="box">
<h2>Register</h2>

<form method="POST">

<input name="full_name" placeholder="Full Name" required>
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>

<select name="role" required onchange="this.form.submit()">
    <option value="">Select Role</option>
    <option value="student" <?= (($_POST['role'] ?? '')=="student")?'selected':'' ?>>Student</option>
    <option value="teacher" <?= (($_POST['role'] ?? '')=="teacher")?'selected':'' ?>>Teacher</option>
    <option value="parent"  <?= (($_POST['role'] ?? '')=="parent")?'selected':'' ?>>Parent</option>
</select>

<?php if (($_POST['role'] ?? '') == "student"): ?>
    <input name="roll_no" placeholder="Roll No" required>
    <input name="class" placeholder="Class" required>
    <input name="mobile_no" placeholder="Mobile No" required>
    <input name="parent_mobile_no" placeholder="Parent Mobile No" required>
<?php endif; ?>

<?php if (($_POST['role'] ?? '') == "teacher"): ?>
    <input name="employee_id" placeholder="Employee ID" required>
    <input name="mobile_no" placeholder="Mobile No" required>
<?php endif; ?>

<?php if (($_POST['role'] ?? '') == "parent"): ?>
    <input name="enrollment_no" placeholder="Enrollment No" required>
    <input name="mobile_no" placeholder="Mobile No" required>
<?php endif; ?>

<button type="submit">Register</button>
</form>

<?php if ($message!=""): ?>
<div class="msg"><?= $message ?></div>
<?php endif; ?>

</div>
</body>
</html>
