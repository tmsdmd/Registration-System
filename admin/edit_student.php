    <?php
session_start();
include 'db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['username'])) {
    header("Location:/Login/admin/dashboard.php");
    exit();
}

// جلب جميع الطلاب
$students = $conn->query("SELECT id, name FROM students")->fetch_all(MYSQLI_ASSOC);

// جلب جميع المقررات
$courses = $conn->query("SELECT id, name, units FROM courses")->fetch_all(MYSQLI_ASSOC);

// معالجة إضافة مقرر منجز
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_completed_course'])) {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    
    // التحقق من وجود المقرر للطالب
    $check_stmt = $conn->prepare("SELECT id FROM student_courses WHERE student_id = ? AND course_id = ?");
    $check_stmt->bind_param("ss", $student_id, $course_id);
    $check_stmt->execute();
    $existing_record = $check_stmt->get_result()->fetch_assoc();
    $check_stmt->close();

    if ($existing_record) {
        // تحديث حالة المقرر
        $update_stmt = $conn->prepare("UPDATE student_courses SET completed = 1 WHERE student_id = ? AND course_id = ?");
        $update_stmt->bind_param("ss", $student_id, $course_id);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        // إضافة مقرر جديد كمنجز
        $insert_stmt = $conn->prepare("INSERT INTO student_courses (student_id, course_id, completed) VALUES (?, ?, 1)");
        $insert_stmt->bind_param("ss", $student_id, $course_id);
        $insert_stmt->execute();
        $insert_stmt->close();
    }
}

// معالجة اختيار الطالب
$selected_student = null;
$completed_courses = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['student_id'])) {
    $student_id = $_POST['student_id'];

    // جلب معلومات الطالب
    $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $selected_student = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // جلب المقررات المنجزة
    $query = "SELECT c.id, c.name, c.units, sc.completed FROM student_courses sc JOIN courses c ON sc.course_id = c.id WHERE sc.student_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $completed_courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>المقررات المنجزة</title>
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
                <a class="nav-link active" href="edit_student.php"><i class="fas fa-check-circle"></i> المقررات المنجزة</a>
            </li>
        </ul>
    </div>

    <div class="content">
        <h2>عرض وإضافة المقررات المنجزة للطالب</h2>
        
        <!-- نموذج اختيار الطالب -->
        <form method="POST" action="" class="mb-4">
            <label for="student_id">اختر الطالب:</label>
            <select name="student_id" class="form-control mb-3" required>
                <option value="">-- اختر طالب --</option>
                <?php foreach ($students as $student): ?>
                    <option value="<?= $student['id'] ?>" <?= (isset($_POST['student_id']) && $_POST['student_id'] == $student['id']) ? 'selected' : '' ?>>
                        <?= $student['name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">عرض</button>
        </form>

        <?php if ($selected_student): ?>
            <!-- نموذج إضافة مقرر منجز -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4>إضافة مقرر منجز</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="student_id" value="<?= $selected_student['id'] ?>">
                        <div class="form-group">
                            <label for="course_id">اختر المقرر:</label>
                            <select name="course_id" class="form-control" required>
                                <option value="">-- اختر مقرر --</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?= $course['id'] ?>"><?= $course['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" name="add_completed_course" class="btn btn-success">إضافة مقرر منجز</button>
                    </form>
                </div>
            </div>

            <!-- عرض المقررات المنجزة -->
            <h3>المقررات المنجزة للطالب: <?= htmlspecialchars($selected_student['name']) ?></h3>
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>رمز المقرر</th>
                        <th>اسم المقرر</th>
                        <th>الوحدات</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($completed_courses as $course): ?>
                        <tr>
                            <td><?= htmlspecialchars($course['id']) ?></td>
                            <td><?= htmlspecialchars($course['name']) ?></td>
                            <td><?= htmlspecialchars($course['units']) ?></td>
                            <td><?= $course['completed'] ? 'مكتمل' : 'غير مكتمل' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
      <!-- JS -->
<script src="/Login/assets/js/jquery-3.5.1.min.js"></script>
<script src="/Login/assets/js/popper.min.js"></script>
<script src="/Login/assets/js/bootstrap.min.js"></script>
<script src="/Login/assets/js/select2.min.js"></script>
</body>
</html
