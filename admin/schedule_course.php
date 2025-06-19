<?php
session_start();
include 'db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['username'])) {
    header("Location:/Login/admin/dashboard.php");
    exit();
}

// Handle adding multiple courses to schedule
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_schedule'])) {
    $course_ids = $_POST['course_id'];
    $day = $_POST['day'];
    $time = $_POST['time'];

    foreach ($course_ids as $course_id) {
        // التحقق من أن المقرر مفتوح قبل الجدولة
        $checkStmt = $conn->prepare("SELECT status FROM courses WHERE id = ?");
        $checkStmt->bind_param("s", $course_id);
        $checkStmt->execute();
        $checkStmt->bind_result($status);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($status == 'open') {
            $stmt = $conn->prepare("INSERT INTO course_schedule (course_id, day, time) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $course_id, $day, $time);
            $stmt->execute();
            $stmt->close();
        } else {
            echo "<div class='alert alert-warning'>لم يتم جدولة المقرر المغلق!</div>";
        }
    }
    echo "<div class='alert alert-success'>تم إضافة المقررات بنجاح!</div>";
}

// Handle removing a course from schedule
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_schedule'])) {
    $schedule_id = $_POST['schedule_id'];
    $stmt = $conn->prepare("DELETE FROM course_schedule WHERE id = ?");
    $stmt->bind_param("i", $schedule_id);
    $stmt->execute();
    $stmt->close();
    echo "<div class='alert alert-success'>تم حذف المقرر بنجاح!</div>";
}

// جلب المقررات المفتوحة فقط للقائمة المنسدلة
$result = $conn->query("SELECT * FROM courses WHERE status = 'open'");
$courses = $result->fetch_all(MYSQLI_ASSOC);

// جلب بيانات الجدول مع schedule_id
$scheduleResult = $conn->query("
    SELECT cs.id as schedule_id, cs.day, cs.time, c.id AS course_id, c.name, c.status 
    FROM course_schedule cs 
    JOIN courses c ON cs.course_id = c.id
    ORDER BY cs.day, cs.time
");

$schedule = [];
while ($row = $scheduleResult->fetch_assoc()) {
    $schedule[$row['day']][$row['time']][] = $row;
}

// تحديد أوقات المحاضرات
$time_slots = [
    '08:00:00' => '08:00 - 10:00',
    '10:00:00' => '10:00 - 12:00',
    '12:00:00' => '12:00 - 14:00',
    '14:00:00' => '14:00 - 16:00',
    '16:00:00' => '16:00 - 18:00',
];
$days = ['السبت', 'الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'];
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>جدول المقررات</title>
       <!-- CSS -->
<link rel="stylesheet" href="/Login/assets/css/bootstrap.min.css">
<link rel="stylesheet" href="/Login/assets/css/all.min.css">
<link rel="stylesheet" href="/Login/assets/css/select2.min.css">

            <link rel="stylesheet" href="/Login/CSS/styles.css">
    <style>
        body {
            font-family: 'Amiri', serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            height: 100vh;
            width: 220px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.5);
        }
        .sidebar a {
            color: #ffffff;
            padding: 15px 20px;
            display: block;
        }
        .sidebar a:hover {
            background-color: #007bff;
            color: white;
        }  
        .content {
            flex-grow: 1;
            padding: 20px;
            margin-left: 240px;
        }
        table {
            width: 100%;
            text-align: center;
        }
        .course-item {
            margin: 3px 0;
            padding: 5px;
            background-color: #f1f1f1;
            border-radius: 4px;
        }
        .course-actions {
            margin-top: 3px;
        }
        .select2-container {
            width: 100% !important;
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
                <a class="nav-link" href="add_course.php"><i class="fas fa-book"></i> قائمة المقررات</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="schedule_course.php"><i class="fas fa-calendar-alt"></i> جدول المقررات</a>
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

    <div class="content">
        <h2>جدول المقررات</h2>

        <!-- نموذج إضافة جدول -->
        <div class="mb-4">
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="course_id">اختر المقررات المفتوحة</label>
                        <select class="form-control select2-multi" id="course_id" name="course_id[]" multiple="multiple" required>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= htmlspecialchars($course['id']) ?>">
                                    <?= htmlspecialchars($course['name']) ?> (<?= htmlspecialchars($course['id']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="day">اليوم</label>
                        <select class="form-control" id="day" name="day" required>
                            <?php foreach ($days as $day): ?>
                                <option value="<?= $day ?>"><?= $day ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="time">الوقت</label>
                        <select class="form-control" id="time" name="time" required>
                            <?php foreach ($time_slots as $key => $value): ?>
                                <option value="<?= $key ?>"><?= $value ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" name="add_schedule" class="btn btn-primary">
                    <i class="fas fa-save"></i> حفظ الجدول
                </button>
                <a href="generate_pdf.php" target="_blank" class="btn btn-secondary">
                    <i class="fas fa-file-pdf"></i> تنزيل PDF
                </a>
            </form>
        </div>

        <!-- جدول المقررات الأسبوعي -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>الوقت</th>
                        <th>السبت</th>
                        <th>الأحد</th>
                        <th>الاثنين</th>
                        <th>الثلاثاء</th>
                        <th>الأربعاء</th>
                        <th>الخميس</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($time_slots as $time_key => $time_value): ?>
                        <tr>
                            <td><?= $time_value ?></td>
                            <?php foreach ($days as $day): ?>
                                <td>
                                    <?php if (isset($schedule[$day][$time_key])): ?>
                                        <?php foreach ($schedule[$day][$time_key] as $course): ?>
                                            <div class="course-item">
                                                <strong><?= htmlspecialchars($course['name']) ?></strong>
                                                <small>(<?= htmlspecialchars($course['course_id']) ?>)</small>
                                                <div class="course-actions">
                                                    <form method="POST" action="" style="display:inline;">
                                                        <input type="hidden" name="schedule_id" value="<?= $course['schedule_id'] ?>">
                                                        <button type="submit" name="remove_schedule" class="btn btn-danger btn-sm">
                                                            <i class="fas fa-trash-alt"></i> حذف
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>



<!-- JS -->
<script src="/Login/assets/js/jquery-3.5.1.min.js"></script>
<script src="/Login/assets/js/popper.min.js"></script>
<script src="/Login/assets/js/bootstrap.min.js"></script>
<script src="/Login/assets/js/select2.min.js"></script>
    
    
    <script>
        $(document).ready(function() {
            // تهيئة Select2 للاختيار المتعدد
            $('.select2-multi').select2({
                placeholder: "اختر المقررات",
                allowClear: true,
                width: 'resolve'
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
