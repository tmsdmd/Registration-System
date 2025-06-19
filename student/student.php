<?php
session_start();
include 'db.php'; // Database connection

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $studentId = $_POST['studentId'];
    $password = $_POST['password'];

    try {
        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT id, name, password FROM students WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("s", $studentId);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Compare passwords directly (no hashing)
            if ($password === $row['password']) {
                // Set session variables
                $_SESSION['student_id'] = $row['id'];
                $_SESSION['name'] = $row['name'];
                
                // Redirect to student dashboard
                header("Location: /Login/student/dashboard.php");
                exit();
            } else {
                $error_message = "كلمة المرور غير صحيحة.";
            }
        } else {
            $error_message = "رقم القيد غير موجود.";
        }
        
        $stmt->close();
    } catch (Exception $e) {
        $error_message = "حدث خطأ في النظام. الرجاء المحاولة لاحقاً.";
        error_log($e->getMessage());
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSS -->
    <link rel="stylesheet" href="/Login/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/Login/assets/css/all.min.css">
    <link rel="stylesheet" href="/Login/assets/css/select2.min.css">
    <link rel="stylesheet" href="/Login/CSS/styles1.css">
    <title>تسجيل دخول الطلاب</title>
  
</head>
<body>
    <nav class="navbar navbar-light bg-light">
        <a class="navbar-brand" href="https://uot.edu.ly/edg/cs/">قسم الحاسوب - كلية التربية قصر بن غشير</a>
        <div class="ml-auto">
            <a href="/Login/index.php" class="btn btn-outline-primary">الرئيسية</a>
        </div>
    </nav>

    <div class="login-container">
        <h2 class="text-center">تسجيل دخول الطالب</h2>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        
        <form method="POST" autocomplete="off">
            <div class="form-group">
                <label for="studentId">رقم القيد</label>
                <input type="text" class="form-control" id="studentId" name="studentId" required>
            </div>
            
            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <div class="password-eye-container">
                    <span class="password-eye-icon" id="togglePassword"><i class="fas fa-eye"></i></span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="كلمة المرور" required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">
                تسجيل الدخول
            </button>
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
    $('#togglePassword').on('click', function () {
        var passwordInput = $('#password');
        var icon = $(this).find('i');
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
