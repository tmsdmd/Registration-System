<?php
session_start();
include 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['student_id'])) {
    die("Error: Student ID not found in the session. Please log in.");
}

// Handle course deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['deleteCourses'])) {
    $student_id = $_SESSION['student_id'];
    $deletedCourses = $_POST['deleteCourses'];
    $stmt = $conn->prepare("DELETE FROM student_courses WHERE student_id = ? AND course_id = ? AND completed = 0");
    foreach ($deletedCourses as $courseId) {
        $stmt->bind_param("is", $student_id, $courseId);
        $stmt->execute();
    }
    $stmt->close();
    $successMessage = "تم حذف المقررات المحددة بنجاح.";
}
// Retrieve student data
$sqlStudentData = "SELECT name, year,semester, class_number FROM students WHERE id = ?";
$stmt = $conn->prepare($sqlStudentData);
$stmt->bind_param("i", $_SESSION['student_id']);
$stmt->execute();
$resultStudentData = $stmt->get_result();
$studentData = $resultStudentData->fetch_assoc();
$stmt->close();

// Retrieve registered (not completed) courses for the student
$sqlStudentCourses = "SELECT c.id, c.name, c.units, sc.day, sc.time 
                     FROM student_courses sc 
                     JOIN courses c ON sc.course_id = c.id 
                     WHERE sc.student_id = ? AND sc.completed = 0";
$stmt = $conn->prepare($sqlStudentCourses);
$stmt->bind_param("i", $_SESSION['student_id']);
$stmt->execute();
$resultStudentCourses = $stmt->get_result();
$studentCourses = [];
$totalUnits = 0;

if ($resultStudentCourses->num_rows > 0) {
    while ($row = $resultStudentCourses->fetch_assoc()) {
        $studentCourses[] = $row;
        $totalUnits += $row['units'];
    }
} else {
    $noCoursesMessage = "لا توجد مقررات مسجلة حالياً.";
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" >
<head>
    <meta charset="UTF-8">
    <title>المقررات المسجلة</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
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
                <a class="nav-link" href="dashboard.php"><i class="fas fa-home"></i> الرئيسية</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="view_courses.php"><i class="fas fa-book"></i> المقررات المتاحة</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="view_registered_courses.php"><i class="fas fa-list"></i> المقررات المسجلة</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="view_schedule.php"><i class="fas fa-calendar-alt"></i> الجدول الدراسي</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="view_classes.php"><i class="fas fa-eye"></i> السجل الأكاديمي</a>
            </li>
        </ul>
    </div>

    <main class="content">
        <div class="container-fluid">
            <div class="header text-right mb-4">
                <h1>المقررات المسجلة</h1>
             <div class="info mb-3">
                    <p>اسم الطالب: <strong><?php echo htmlspecialchars($studentData['name']); ?></strong></p>
                    <p>رقم القيد: <strong><?php echo htmlspecialchars($_SESSION['student_id']); ?></strong></p>
                    <p>الفصل الدراسي: <strong><?php echo htmlspecialchars($studentData['semester']); ?></strong></p>
                                                <p> السنة الدراسية: <strong><?php echo htmlspecialchars($studentData['year']); ?></strong></p>
                    <?php if (!empty($studentData['class_number'])): ?>
                        <p>رقم الفصل: <strong><?php echo htmlspecialchars($studentData['class_number']); ?></strong></p>
                    <?php endif; ?>
                </div>

                <?php if (isset($successMessage)): ?>
                    <div class="alert alert-success"><?php echo $successMessage; ?></div>
                <?php endif; ?>

                <?php if (!empty($studentCourses)): ?>
                <form method="POST">
                    <table class="table table-bordered table-striped table-hover text-center">
                        <thead class="thead-dark">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">اسم المقرر</th>
                                <th scope="col">رمز المقرر</th>
                                <th scope="col">اليوم</th>
                                <th scope="col">الوقت</th>
                                <th scope="col">الوحدات</th>
                                <th scope="col">اختيار</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($studentCourses as $index => $course): ?>
                                <tr>
                                    <th scope="row"><?php echo $index + 1; ?></th>
                                    <td><?php echo htmlspecialchars($course['name']); ?></td>
                                    <td><?php echo htmlspecialchars($course['id']); ?></td>
                                    <td><?php echo htmlspecialchars($course['day'] ?? 'غير محدد'); ?></td>
                                    <td><?php echo htmlspecialchars($course['time'] ?? 'غير محدد'); ?></td>
                                    <td><?php echo htmlspecialchars($course['units']); ?></td>
                                    <td><input type="checkbox" name="deleteCourses[]" value="<?php echo $course['id']; ?>"></td>

                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                        <a href="view_courses.php" class="btn btn-secondary mb-2"><i class="fas fa-arrow-left"></i> رجوع</a>
                        <div>
                            <button type="submit" class="btn btn-danger mr-2 mb-2"><i class="fas fa-trash-alt"></i> حذف المحدد</button>
                            <a href="generate_registered_courses_pdf.php" target="_blank" class="btn btn-primary mb-2"><i class="fas fa-file-pdf"></i> تصدير PDF</a>
                        </div>
                    </div>

                    <div class="alert alert-info text-right" role="alert">
                        <i class="fas fa-info-circle"></i> مجموع الوحدات المسجلة: <strong><?php echo $totalUnits; ?></strong>
                    </div>
                </form>
                <?php else: ?>
                    <div class="alert alert-warning text-center">لا توجد مقررات مسجلة حالياً.</div>
                <?php endif; ?>
            </div>
        </div>
    </main>
<!-- JS -->
<script src="/Login/assets/js/jquery-3.5.1.min.js"></script>
<script src="/Login/assets/js/popper.min.js"></script>
<script src="/Login/assets/js/bootstrap.min.js"></script>
<script src="/Login/assets/js/select2.min.js"></script>
    
</body>
</html>

