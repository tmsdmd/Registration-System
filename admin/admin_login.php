<?php
session_start();
include 'db.php'; // Ensure database connection is correct
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Debugging: Input values
    // echo "Username entered: $user<br>";
    // echo "Password entered: $pass<br>";

    // Prepare the query
    $stmt = $conn->prepare("SELECT password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // echo "User found.<br>";
        $row = $result->fetch_assoc();
        $stored_password = $row['password'];
        // echo "Password in database: $stored_password<br>";

        // Direct plain-text comparison
        if ($pass === $stored_password) {
            // echo "Password matches.<br>";
            $_SESSION['username'] = $user;
            header("Location: dashboard.php");
            exit();
        } else {
            echo "<div class='alert alert-danger text-center'>كلمة المرور غير صحيحة.</div>";
        }
    } else {
        echo "<div class='alert alert-danger text-center'>لا يوجد مستخدم بهذا الاسم.</div>";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="/Login/assets/css/bootstrap.min.css">
<link rel="stylesheet" href="/Login/assets/css/all.min.css">
<link rel="stylesheet" href="/Login/assets/css/select2.min.css">
<link rel="stylesheet" href="/Login/CSS/styles1.css">
<title> المسؤول</title>

</head>
<body>

<nav class="navbar navbar-light bg-light">
<a class="navbar-brand" href="https://uot.edu.ly/edg/cs/">قسم الحاسوب - كلية التربية قصر بن غشير</a>
<div class="ml-auto">
<a href="/Login/index.php" class="btn btn-outline-primary">الرئيسية</a>
</div>
</nav>

<div class="login-container">
<h2 class="text-center">تسجيل دخول المسؤول</h2>
<form action="admin_login.php" method="POST">

<div class="form-group">
<label for="username">اسم المسؤول</label>
<input type="text" class="form-control" id="username" name="username" required>
</div>

<div class="form-group">
<label for="password">كلمة المرور</label>
<!-- أيقونة العين فوق مربع كلمة المرور -->
<div class="password-eye-container">
    <span class="password-eye-icon" id="togglePassword"><i class="fas fa-eye"></i></span>
    <input type="password" class="form-control" id="password" name="password" placeholder="كلمة المرور" required>
</div>
</div>

<button type="submit" class="btn btn-primary btn-block">تسجيل الدخول</button>

<div class="mt-4" align="center" dir="rtl">
    تم التطوير بواسطة <a href="mailto:whysot.tech@gmail.com" title="Contact WhySoTech">WhySoTech</a>
</div>

</form>
</div>

<!-- JS -->
<script src="/Login/assets/js/jquery-3.5.1.min.js"></script>
<script src="/Login/assets/js/popper.min.js"></script>
<script src="/Login/assets/js/bootstrap.min.js"></script>
<script src="/Login/assets/js/select2.min.js"></script>
<script>
$('#togglePassword').on('click', function() {
    const passwordInput = $('#password');
    const icon = $(this).find('i');
    if (passwordInput.attr('type') === 'password') {
        passwordInput.attr('type', 'text');
        icon.removeClass('fa-eye').addClass('fa-eye-slash');
    } else {
        passwordInput.attr('type', 'password');
        icon.removeClass('fa-eye-slash').addClass('fa-eye');
    }
});
</script>
</body>
</html>
