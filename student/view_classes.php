<?php
session_start();
include 'db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// جلب بيانات الطالب
$student_stmt = $conn->prepare("SELECT name, year, semester, class_number FROM students WHERE id = ?");
$student_stmt->bind_param("s", $student_id);
$student_stmt->execute();
$student_result = $student_stmt->get_result();
$student_data = $student_result->fetch_assoc();
$student_stmt->close();

// جلب المقررات المنجزة
$courses_stmt = $conn->prepare("SELECT c.id, c.name, c.units 
                               FROM student_courses sc
                               JOIN courses c ON sc.course_id = c.id
                               WHERE sc.student_id = ? AND sc.completed = 1
                               ORDER BY c.name");
$courses_stmt->bind_param("s", $student_id);
$courses_stmt->execute();
$courses_result = $courses_stmt->get_result();

// جلب إجمالي الساعات المنجزة
$total_units = $conn->query("
    SELECT SUM(c.units) as total 
    FROM student_courses sc
    JOIN courses c ON sc.course_id = c.id
    WHERE sc.student_id = '$student_id' AND sc.completed = 1
")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>السجل الأكاديمي</title>
          <!-- CSS -->
<link rel="stylesheet" href="/Login/assets/css/bootstrap.min.css">
<link rel="stylesheet" href="/Login/assets/css/all.min.css">
<link rel="stylesheet" href="/Login/assets/css/select2.min.css">
      <link rel="stylesheet" href="/Login/CSS/styles.css">
   
</head>

<body>
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


    <div class="content">
        <div class="header text-right mb-4">
            <h1>السجل الأكاديمي</h1>
            <div class="info">
                اسم الطالب: <strong><?php echo htmlspecialchars($student_data['name']); ?></strong><br>
                رقم القيد: <strong><?php echo htmlspecialchars($student_id); ?></strong><br>
                الفصل الدراسي: <strong><?php echo htmlspecialchars($student_data['semester']); ?></strong><br>
                السنة الدراسية: <strong><?php echo htmlspecialchars($student_data['year']); ?></strong><br>
                <?php if (isset($student_data['class_number'])): ?>
                    رقم الفصل: <strong><?php echo htmlspecialchars($student_data['class_number']); ?></strong>
                <?php endif; ?>
            </div>
        </div>

        <h4 class="text-right mb-3">المقررات المنجزة</h4>
        
        <?php if ($courses_result->num_rows > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>اسم المقرر</th>
                        <th>عدد الوحدات</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $counter = 1; while ($course = $courses_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $counter++ ?></td>
                            <td><?= htmlspecialchars($course['name']) ?></td>
                            <td><?= htmlspecialchars($course['units']) ?></td>
                            <td><span class="badge-completed"><i class="fas fa-check"></i> مكتمل</span></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <div class="text-right total-hours">
                <strong>إجمالي الوحدات المنجزة:</strong> 
                <?= $total_units ?: '0' ?> وحدة
            </div>
        <?php else: ?>
            <div class="alert alert-info text-right">
                لا توجد مقررات منجزة  حتى الآن.
            </div>
        <?php endif; ?>
    </div>

   <!-- JS -->
<script src="/Login/assets/js/jquery-3.5.1.min.js"></script>
<script src="/Login/assets/js/popper.min.js"></script>
<script src="/Login/assets/js/bootstrap.min.js"></script>
<script src="/Login/assets/js/select2.min.js"></script>
    
</body>
</html>
