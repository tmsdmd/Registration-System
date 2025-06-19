<?php
session_start();
include 'db.php'; // Database connection
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location:/Login/admin/dashboard.php"); // Redirect to login page if not logged in
    exit();
}

// Get current date and time in UTC
$current_datetime = date('Y-m-d H:i:s');

// Count total students
$sqlStudents = "SELECT COUNT(*) as count FROM students";
$resultStudents = $conn->query($sqlStudents);
$totalStudents = $resultStudents->fetch_assoc()['count'];

// Count total courses
$sqlCourses = "SELECT COUNT(*) as count FROM courses";
$resultCourses = $conn->query($sqlCourses);
$totalCourses = $resultCourses->fetch_assoc()['count'];

// Count total registrations
$sqlRegistrations = "SELECT COUNT(*) as count FROM student_courses";
$resultRegistrations = $conn->query($sqlRegistrations);
$totalRegistrations = $resultRegistrations->fetch_assoc()['count'];

// Handle password change form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        echo "<script>alert('كلمتا المرور غير متطابقتين');</script>";
    } else {
        $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE username = ?");
        $stmt->execute([$new_password, $_SESSION['username']]);
        echo "<script>alert('تم تغيير كلمة المرور بنجاح');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم المسؤول</title>
  <link rel="stylesheet" href="/Login/assets/css/bootstrap.min.css">
<link rel="stylesheet" href="/Login/assets/css/all.min.css">
<link rel="stylesheet" href="/Login/assets/css/select2.min.css">
    <link rel="stylesheet" href="/Login/CSS/styles.css">
    <style>
        .datetime-box {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: right;
        }
        .datetime-box i {
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <a class="navbar-brand" href="#">نظام تسجيل المقررات</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#changePasswordModal">تغيير كلمة المرور</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="admin_login.php">تسجيل الخروج</a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>
    
    <div class="sidebar">
        <h3>نظام تسجيل المقررات</h3>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php"><i class="fas fa-home"></i> الرئيسية</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="add_course.php"><i class="fas fa-book"></i> قائمة المقررات</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="schedule_course.php"><i class="fas fa-calendar-alt"></i> جدول المقررات</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="add_student.php"><i class="fas fa-users"></i> إضافة طالب</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="add_admin.php"><i class="fas fa-user-shield"></i> إضافة مسؤول</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="edit_student.php"><i class="fas fa-check-circle"></i> المقررات المنجزة</a>
            </li>
        </ul>
    </div>

    <main role="main" dir="rtl">
        <!-- Date/Time Box -->
        <div class="datetime-box">
            <p class="mb-0">
                <i class="fas fa-clock"></i> التاريخ والوقت: <?php echo htmlspecialchars($current_datetime); ?>
            </p>
        </div>

        <div class="welcome-box">
            <h3>مرحباً بعودتك، <?php echo htmlspecialchars($_SESSION['username']); ?></h3>
            <p class="mb-0">مرحباً بك في لوحة تحكم المسؤول</p>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-4">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-users"></i> إجمالي الطلاب</h5>
                        <p class="card-text display-4"><?php echo $totalStudents; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-book"></i> إجمالي المقررات</h5>
                        <p class="card-text display-4"><?php echo $totalCourses; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-clipboard-list"></i> إجمالي التسجيلات</h5>
                        <p class="card-text display-4"><?php echo $totalRegistrations; ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-bolt"></i> إجراءات سريعة</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <a href="add_course.php" class="btn btn-outline-primary btn-block">
                                    <i class="fas fa-plus"></i> إضافة مقرر جديد
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="add_student.php" class="btn btn-outline-success btn-block">
                                    <i class="fas fa-user-plus"></i> إضافة طالب جديد
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="schedule_course.php" class="btn btn-outline-info btn-block">
                                    <i class="fas fa-calendar-plus"></i> جدولة مقرر
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="edit_student.php" class="btn btn-outline-secondary btn-block">
                                    <i class="fas fa-check-circle"></i> تحديث المقررات المنجزة
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- System Management -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-cog"></i> إدارة النظام</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <a href="add_admin.php" class="btn btn-link">
                                    <i class="fas fa-user-shield"></i> إدارة المسؤولين
                                </a>
                            </li>
                            <li class="list-group-item">
                                <a href="#" class="btn btn-link">
                                    <i class="fas fa-database"></i> نسخ احتياطي للنظام
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-chart-bar"></i> إحصائيات النظام</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                معدل التسجيل في المقررات
                                <span class="badge badge-primary badge-pill">
                                    <?php echo number_format($totalRegistrations / ($totalStudents ?: 1), 1); ?>
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                متوسط المقررات لكل طالب
                                <span class="badge badge-primary badge-pill">
                                    <?php echo number_format($totalRegistrations / ($totalStudents ?: 1), 1); ?>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="changePasswordModalLabel">تغيير كلمة المرور</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="new_password">كلمة المرور الجديدة</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">تأكيد كلمة المرور</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            <small class="text-danger" id="passwordError" style="display:none;">كلمتا المرور غير متطابقتين</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                        <button type="submit" name="change_password" class="btn btn-primary" id="submitBtn">حفظ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="mt-4" align="center" dir="rtl">
        تم التطوير بواسطة <a href="mailto:whysot.tech@gmail.com" title="Contact WhySoTech">WhySoTech</a>
    </div>

   <!-- JS -->
<script src="/Login/assets/js/jquery-3.5.1.min.js"></script>
<script src="/Login/assets/js/popper.min.js"></script>
<script src="/Login/assets/js/bootstrap.min.js"></script>
<script src="/Login/assets/js/select2.min.js"></script>
    
    
    <script>
    $(document).ready(function() {
        // Password matching validation
        $('#confirm_password').on('keyup', function() {
            if ($('#new_password').val() !== $('#confirm_password').val()) {
                $('#passwordError').show();
                $('#confirm_password').addClass('password-mismatch');
                $('#submitBtn').prop('disabled', true);
            } else {
                $('#passwordError').hide();
                $('#confirm_password').removeClass('password-mismatch');
                $('#submitBtn').prop('disabled', false);
            }
        });
    });
    </script>
</body>
</html>

<?php
$conn->close();
?>
