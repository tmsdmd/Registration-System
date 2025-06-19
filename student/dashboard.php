<?php
session_start();
include 'db.php'; // Database connection file

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: student.php"); // Redirect to login page if not logged in
    exit();
}

$student_id = $_SESSION['student_id'];

// Get current date and time in UTC
$current_datetime = date('Y-m-d H:i:s');
$current_user = $_SESSION['student_id']; // Using student_id as the login

// Retrieve student data
$sqlStudentData = "SELECT name, year, semester, class_number FROM students WHERE id = ?";
$stmt = $conn->prepare($sqlStudentData);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$resultStudentData = $stmt->get_result();
$studentData = $resultStudentData->fetch_assoc();
$stmt->close();

// Count registered courses
$sqlRegistered = "SELECT COUNT(*) as count FROM student_courses WHERE student_id = ? AND completed = 0";
$stmt = $conn->prepare($sqlRegistered);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$resultRegistered = $stmt->get_result();
$registeredCount = $resultRegistered->fetch_assoc()['count'];
$stmt->close();

// Count passed courses
$sqlPassed = "SELECT COUNT(*) as count FROM student_courses WHERE student_id = ? AND completed = 1";
$stmt = $conn->prepare($sqlPassed);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$resultPassed = $stmt->get_result();
$passedCount = $resultPassed->fetch_assoc()['count'];
$stmt->close();
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم الطالب</title>
          <!-- CSS -->
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
                        <?php echo htmlspecialchars($studentData['name']); ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#changePasswordModal">تغيير كلمة المرور</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="student.php">تسجيل الخروج</a>
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
                <a class="nav-link" href="view_courses.php"><i class="fas fa-book"></i> المقررات المتاحة</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="view_registered_courses.php"><i class="fas fa-list"></i> المقررات المسجلة</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="view_schedule.php"><i class="fas fa-calendar-alt"></i> الجدول الدراسي</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="view_classes.php"><i class="fas fa-eye"></i> السجل الأكاديمي</a>
            </li>
        </ul>
    </div>

    <main role="main" dir="rtl">
        <!-- Date/Time and User Info Box -->
        <div class="datetime-box">
            <p class="mb-0">
                <i class="fas fa-clock"></i> التاريخ والوقت: <?php echo htmlspecialchars($current_datetime); ?>
            </p>
          
        </div>

        <div class="welcome-box">
            <h3>مرحباً بعودتك، <?php echo htmlspecialchars($studentData['name']); ?></h3>
            <p class="mb-0">يمكنك من هنا إدارة مقرراتك الدراسية ومتابعة سجلك الأكاديمي</p>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-id-card"></i> رقم القيد</h5>
                        <p class="card-text display-4"><?php echo htmlspecialchars($student_id); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-book-open"></i> المقررات المسجلة</h5>
                        <p class="card-text display-4"><?php echo $registeredCount; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-check-circle"></i> المقررات المنجزة</h5>
                        <p class="card-text display-4"><?php echo $passedCount; ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-info-circle"></i> المعلومات الأكاديمية</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                السنة الدراسية
                                <span class="badge badge-primary badge-pill"><?php echo htmlspecialchars($studentData['year']); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                الفصل الدراسي
                                <span class="badge badge-primary badge-pill"><?php echo htmlspecialchars($studentData['semester']); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                رقم الفصل
                                <span class="badge badge-primary badge-pill"><?php echo htmlspecialchars($studentData['class_number']); ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-history"></i> السجل الأكاديمي</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">لعرض الفصول الدراسية السابقة والنتائج.</p>
                        <a href="view_classes.php" class="btn btn-primary btn-block">
                            <i class="fas fa-eye"></i> عرض السجل
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-bolt"></i> إجراءات سريعة</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <a href="view_courses.php" class="btn btn-outline-primary btn-block">
                                    <i class="fas fa-search"></i> استعراض المقررات
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="view_registered_courses.php" class="btn btn-outline-success btn-block">
                                    <i class="fas fa-list"></i> المقررات المسجلة
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="view_schedule.php" class="btn btn-outline-info btn-block">
                                    <i class="fas fa-calendar"></i> الجدول الدراسي
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="view_classes.php" class="btn btn-outline-secondary btn-block">
                                    <i class="fas fa-eye"></i> السجل الأكاديمي
                                </a>
                            </div>
                        </div>
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
