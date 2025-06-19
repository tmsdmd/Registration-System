<?php
session_start();
include 'db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// جلب بيانات الطالب
$student_stmt = $conn->prepare("SELECT name,year, semester, class_number FROM students WHERE id = ?");
$student_stmt->bind_param("s", $student_id);
$student_stmt->execute();
$student_result = $student_stmt->get_result();
$student_data = $student_result->fetch_assoc();
$student_stmt->close();

// جلب جدول المقررات الحالية (غير المكتملة)
$schedule_stmt = $conn->prepare("
    SELECT c.name AS course_name, sc.day, sc.time
    FROM student_courses sc
    JOIN courses c ON sc.course_id = c.id
    WHERE sc.student_id = ? AND sc.completed = 0
    ORDER BY FIELD(sc.day, 'السبت', 'الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة'), sc.time
");
$schedule_stmt->bind_param("s", $student_id);
$schedule_stmt->execute();
$schedule_result = $schedule_stmt->get_result();
$schedule = [];
while ($row = $schedule_result->fetch_assoc()) {
    $schedule[] = $row;
}
$schedule_stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>الجدول الدراسي</title>
         <!-- CSS -->
<link rel="stylesheet" href="/Login/assets/css/bootstrap.min.css">
<link rel="stylesheet" href="/Login/assets/css/all.min.css">
<link rel="stylesheet" href="/Login/assets/css/select2.min.css">
    <link rel="stylesheet" href="/Login/CSS/styles.css" />
    <style>
     .download-link {
            display: inline-block;
            margin-top: 10px;
            background-color: #007bff;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            text-decoration: none;
        }
        .download-link:hover {
            background-color: #0056b3;
            color: white;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3>نظام تسجيل المقررات</h3>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php"><i class="fas fa-home"></i> الرئيسية</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="view_courses.php"><i class="fas fa-book"></i> المقررات المتاحة</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="view_registered_courses.php"><i class="fas fa-list"></i> المقررات المسجلة</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="view_schedule.php"><i class="fas fa-calendar-alt"></i> الجدول الدراسي</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="view_classes.php"><i class="fas fa-eye"></i> السجل الأكاديمي</a>
            </li>
        </ul>
    </div>

    <div class="content">
        <div class="header mb-4 text-right">
            <h1>الجدول الدراسي</h1>
            <div class="info">
                اسم الطالب: <strong><?= htmlspecialchars($student_data['name']) ?></strong><br>
                رقم القيد: <strong><?= htmlspecialchars($student_id) ?></strong><br>
          الفصل الدراسي: <strong><?= htmlspecialchars($student_data['semester']) ?></strong><br>
                              السنة الدراسية: <strong><?= htmlspecialchars($student_data['year']) ?></strong><br>
                              
                <?php if (!empty($student_data['class_number'])): ?>
                    رقم الفصل: <strong><?= htmlspecialchars($student_data['class_number']) ?></strong>
                <?php endif; ?>
            </div>
            <a href="generate_student_schedule_pdf.php" target="_blank" class="download-link">تنزيل جدول الدراسي PDF</a>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>اسم المقرر</th>
                    <th>اليوم</th>
                    <th>الوقت</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($schedule)): ?>
                    <tr>
                        <td colspan="3">لا توجد مقررات مسجلة حالياً.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($schedule as $course): ?>
                        <tr>
                            <td><?= htmlspecialchars($course['course_name']) ?></td>
                            <td><?= htmlspecialchars($course['day']) ?></td>
                            <td><?= htmlspecialchars($course['time']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<!-- JS -->
<script src="/Login/assets/js/jquery-3.5.1.min.js"></script>
<script src="/Login/assets/js/popper.min.js"></script>
<script src="/Login/assets/js/bootstrap.min.js"></script>
<script src="/Login/assets/js/select2.min.js"></script>
    
</body>
</html>

