<?php
session_start();
$messages = array(); // Array to store all messages
include 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['student_id'])) {
    die("الرجاء تسجيل الدخول أولاً.");
}

$student_id = $_SESSION['student_id'];

// Fetch student info
$studentData = [];
$stmt = $conn->prepare("SELECT name, year,semester, class_number FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$studentData = $result->fetch_assoc();
$stmt->close();

// Fetch available courses
$sql = "SELECT c.id, c.name, c.units 
        FROM courses c
        WHERE c.status = 'open' 
        AND c.id NOT IN (
            SELECT course_id FROM student_courses WHERE student_id = ?
        )";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$courses = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Modify the form handling section
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addCourses'])) {
    $selectedCourses = $_POST['addCourses'];
    $success = true;

    foreach ($selectedCourses as $courseId) {
        // 1. Check course availability
        $checkAvailable = $conn->prepare("SELECT id FROM courses WHERE id = ? AND status = 'open'");
        $checkAvailable->bind_param("s", $courseId);
        $checkAvailable->execute();
        if ($checkAvailable->get_result()->num_rows === 0) {
            $messages[] = array('type' => 'danger', 'text' => "المقرر غير متاح: " . htmlspecialchars($courseId));
            $checkAvailable->close();
            $success = false;
            continue;
        }
        $checkAvailable->close();

        // 2. Check if already registered
        $checkRegistered = $conn->prepare("SELECT 1 FROM student_courses WHERE student_id = ? AND course_id = ?");
        $checkRegistered->bind_param("is", $student_id, $courseId);
        $checkRegistered->execute();
        if ($checkRegistered->get_result()->num_rows > 0) {
            $messages[] = array('type' => 'warning', 'text' => "المقرر مُسجل بالفعل: " . htmlspecialchars($courseId));
            $checkRegistered->close();
            continue;
        }
        $checkRegistered->close();

        // 3. Check prerequisites
        $reqStmt = $conn->prepare("SELECT r.Requirements_id, c.name 
                                   FROM Requirements r 
                                   JOIN courses c ON r.Requirements_id = c.id 
                                   WHERE r.course_id = ?");
        $reqStmt->bind_param("s", $courseId);
        $reqStmt->execute();
        $reqResult = $reqStmt->get_result();
        $requirementsMet = true;
        $missing = [];

        while ($req = $reqResult->fetch_assoc()) {
            $checkCompleted = $conn->prepare("SELECT 1 FROM student_courses 
                                              WHERE student_id = ? AND course_id = ? AND completed = 1");
            $checkCompleted->bind_param("is", $student_id, $req['Requirements_id']);
            $checkCompleted->execute();
            if ($checkCompleted->get_result()->num_rows === 0) {
                $requirementsMet = false;
                $missing[] = $req['name'];
            }
            $checkCompleted->close();
        }
        $reqStmt->close();

        if (!$requirementsMet) {
            $messages[] = array('type' => 'warning', 'text' => "لا يمكن التسجيل في " . htmlspecialchars($courseId) .
                 "، لم تُكمل: " . htmlspecialchars(implode("، ", $missing)));
            $success = false;
            continue;
        }

        // 4. Get course schedule
        $scheduleStmt = $conn->prepare("SELECT day, time FROM course_schedule WHERE course_id = ?");
        $scheduleStmt->bind_param("s", $courseId);
        $scheduleStmt->execute();
        $schedule = $scheduleStmt->get_result()->fetch_assoc();
        $scheduleStmt->close();

        if (!$schedule) {
            $messages[] = array('type' => 'danger', 'text' => "لا يوجد جدول لهذا المقرر: " . htmlspecialchars($courseId));
            $success = false;
            continue;
        }

        // 5. Register course
        $registerStmt = $conn->prepare("INSERT INTO student_courses 
                                        (student_id, course_id, day, time, completed) 
                                        VALUES (?, ?, ?, ?, 0)");
        $registerStmt->bind_param("isss", $student_id, $courseId, $schedule['day'], $schedule['time']);
        if (!$registerStmt->execute()) {
            $messages[] = array('type' => 'danger', 'text' => "فشل تسجيل المقرر: " . htmlspecialchars($courseId) . " - " . htmlspecialchars($registerStmt->error));
            $success = false;
        }
        $registerStmt->close();
    }

    if ($success) {
        $_SESSION['registration_success'] = true;
        header("Location: view_registered_courses.php");
        exit;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>المقررات المتاحة</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
          <!-- CSS -->
<link rel="stylesheet" href="/Login/assets/css/bootstrap.min.css">
<link rel="stylesheet" href="/Login/assets/css/all.min.css">
<link rel="stylesheet" href="/Login/assets/css/select2.min.css">
    <link rel="stylesheet" href="/Login/CSS/styles.css">
</head>


<style>
    .messages-container {
        max-height: 300px;
        overflow-y: auto;
        padding: 15px;
        border-radius: 5px;
    }
    .messages-container .alert {
        margin-bottom: 10px;
    }
    .alert-heading {
        margin-bottom: 15px;
        text-align: right;
    }
</style>
<body>
    <div class="sidebar">
        <h3>نظام تسجيل المقررات</h3>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php"><i class="fas fa-home"></i> الرئيسية</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="view_courses.php"><i class="fas fa-book"></i> المقررات المتاحة</a>
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

    <main class="content">
        <div class="container-fluid">
            <div class="header text-right mb-4">
                <h1>المقررات المتاحة</h1>
                <div class="info mb-3">
                    <p>اسم الطالب: <strong><?php echo htmlspecialchars($studentData['name']); ?></strong></p>
                    <p>رقم القيد: <strong><?php echo htmlspecialchars($student_id); ?></strong></p>
                    <p>الفصل الدراسي: <strong><?php echo htmlspecialchars($studentData['semester']); ?></strong></p>
                            <p> السنة الدراسية: <strong><?php echo htmlspecialchars($studentData['year']); ?></strong></p>
                    <?php if (!empty($studentData['class_number'])): ?>
                        <p>رقم الفصل: <strong><?php echo htmlspecialchars($studentData['class_number']); ?></strong></p>
                    <?php endif; ?>
                </div>
<div class="messages-container mb-4">
    <?php if (!empty($messages)): ?>
        <div class="alert alert-info">
            <h4 class="alert-heading">   نتائج عملية التسجيل </h4>
            <?php foreach ($messages as $message): ?>
                <div class="alert alert-<?php echo $message['type']; ?> mb-2">
                    <?php echo $message['text']; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

                <?php if (empty($courses)): ?>
                    <div class="alert alert-warning text-center">لا توجد مقررات متاحة للتسجيل حالياً.</div>
                <?php else: ?>
                    <form method="POST">
                        <table class="table table-bordered table-striped table-hover text-center">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">اسم المقرر</th>
                                    <th scope="col">رمز المقرر</th>
                                    <th scope="col">الوحدات</th>
                                    <th scope="col">اختيار</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($courses as $index => $course): ?>
                                    <tr>
                                        <th scope="row"><?php echo $index + 1; ?></th>
                                        <td><?php echo htmlspecialchars($course['name']); ?></td>
                                        <td><?php echo htmlspecialchars($course['id']); ?></td>
                                        <td><?php echo htmlspecialchars($course['units']); ?></td>
                                        <td><input type="checkbox" name="addCourses[]" value="<?php echo htmlspecialchars($course['id']); ?>"></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                            <a href="dashboard.php" class="btn btn-secondary mb-2"><i class="fas fa-arrow-left"></i> رجوع</a>
                            <button type="submit" class="btn btn-success mb-2"><i class="fas fa-plus"></i> إضافة المقررات المحددة</button>
                        </div>
                    </form>
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
