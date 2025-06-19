<?php
session_start();
include 'db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['username'])) {
    header("Location:/Login/admin/dashboard.php");
    exit();
}

// In your student insertion code section, modify the query to use ID as password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_student'])) {
    $student_id = $_POST['student_id'];
    $student_name = $_POST['student_name'];
    $semester = $_POST['semester'];
    $year = $_POST['year'];
    $class_number = $_POST['class_number'];
    $password = $student_id;

    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM students WHERE id = ?");
    $checkStmt->bind_param("s", $student_id);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        echo "<div class='alert alert-danger'>هذا المعرف موجود بالفعل!</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO students (id, name, year, semester, class_number, password) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $student_id, $student_name, $year, $semester, $class_number, $password);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>تم إضافة الطالب بنجاح!</div>";
        } else {
            echo "<div class='alert alert-danger'>حدث خطأ أثناء إضافة الطالب: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}

// Edit student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_student'])) {
    $student_id = $_POST['student_id'];
    $student_name = $_POST['student_name'];
    $semester = $_POST['semester'];
    $year = $_POST['year'];
    $class_number = $_POST['class_number'];

    $stmt = $conn->prepare("UPDATE students SET name = ?, year = ?, semester = ?, class_number = ? WHERE id = ?");
    if (!$stmt) {
        echo "<div class='alert alert-danger'>Error preparing update statement: " . $conn->error . "</div>";
        exit();
    }

    $stmt->bind_param("sssss", $student_name, $year, $semester, $class_number, $student_id);
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>تم تحديث الطالب بنجاح!</div>";
    } else {
        echo "<div class='alert alert-danger'>حدث خطأ أثناء تحديث الطالب: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// Delete student
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    if (!$stmt) {
        echo "<div class='alert alert-danger'>Error preparing delete statement: " . $conn->error . "</div>";
        exit();
    }

    $stmt->bind_param("s", $id);
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>تم حذف الطالب بنجاح!</div>";
    } else {
        echo "<div class='alert alert-danger'>حدث خطأ أثناء حذف الطالب: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// Fetch all students
$result = $conn->query("SELECT * FROM students");
if (!$result) {
    echo "<div class='alert alert-danger'>Error fetching students: " . $conn->error . "</div>";
    exit();
}
$students = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة طالب</title>
   <link rel="stylesheet" href="/Login/assets/css/bootstrap.min.css">
<link rel="stylesheet" href="/Login/assets/css/all.min.css">
<link rel="stylesheet" href="/Login/assets/css/select2.min.css">
            <link rel="stylesheet" href="/Login/CSS/styles.css">
    <style>
  
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
        <h2>إضافة طالب</h2>

        <!-- Add Student Form -->
        <div class="mb-4">
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="student_id">رقم القيد</label>
                        <input type="text" class="form-control" id="student_id" name="student_id" maxlength="10" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="student_name">اسم الطالب</label>
                        <input type="text" class="form-control" id="student_name" name="student_name" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="semester">الفصل الدراسي</label>
                        <input type="text" class="form-control" id="semester" name="semester" required>
                    </div>
                       <div class="form-group col-md-3">
                        <label for="year">السنة الدراسية</label>
                        <input type="text" class="form-control" id="year" name="year" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="class_number">رقم الفصل</label>
                        <input type="text" class="form-control" id="class_number" name="class_number" required>
                    </div>
                </div>
                <button type="submit" name="add_student" class="btn btn-primary">حفظ</button>
            </form>
        </div>

        <!-- Student List Table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                     <th>#</th>
                    <th>رقم القيد</th>
                    <th>اسم الطالب</th>
                    <th>الفصل الدراسي</th>
                                        <th>السنة الدراسية</th>
                    <th>رقم الفصل</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $index => $student): ?>
                    <tr>
                          <td><?= $index + 1 ?></td> 
                        <td><?= htmlspecialchars($student['id']) ?></td>
                        <td><?= htmlspecialchars($student['name']) ?></td>
                        <td><?= htmlspecialchars($student['semester']) ?></td>
                                                <td><?= htmlspecialchars($student['year']) ?></td>
                        <td><?= htmlspecialchars($student['class_number']) ?></td>
                        <td>
                            <a href="?edit=<?= htmlspecialchars($student['id']) ?>" class="btn btn-warning btn-sm">تعديل</a>
                            <a href="?delete=<?= htmlspecialchars($student['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد أنك تريد حذف هذا الطالب؟');">حذف</a>
                        </td>
                    </tr>
                    <!-- Edit Form for Each Student -->
                    <tr id="edit-form-<?= htmlspecialchars($student['id']) ?>" style="display: none;">
                        <td colspan="5">
                            <form method="POST" action="">
                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <input type="hidden" name="student_id" value="<?= htmlspecialchars($student['id']) ?>">
                                        <label for="student_name">اسم الطالب</label>
                                        <input type="text" class="form-control" name="student_name" value="<?= htmlspecialchars($student['name']) ?>" required>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="semester">الفصل الدراسي</label>
                                        <input type="text" class="form-control" name="semester" value="<?= htmlspecialchars($student['semester']) ?>" required>
                                    </div>
                                                 <div class="form-group col-md-3">
                                        <label for="year">السنة الدراسية</label>
                                        <input type="text" class="form-control" name="year" value="<?= htmlspecialchars($student['year']) ?>" required>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="class_number">رقم الفصل</label>
                                        <input type="text" class="form-control" name="class_number" value="<?= htmlspecialchars($student['class_number']) ?>" required>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <button type="submit" name="edit_student" class="btn btn-primary">تحديث</button>
                                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('edit-form-<?= htmlspecialchars($student['id']) ?>').style.display='none'">إلغاء</button>
                                    </div>
                                </div>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<!-- JS -->
<script src="/Login/assets/js/jquery-3.5.1.min.js"></script>
<script src="/Login/assets/js/popper.min.js"></script>
<script src="/Login/assets/js/bootstrap.min.js"></script>
<script src="/Login/assets/js/select2.min.js"></script>
    
    
    <script>
        // Show edit form when edit button is clicked  فورم التعديل معلومات الطالب
        document.querySelectorAll('.btn-warning').forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                const studentId = this.href.split('=')[1];
                const editForm = document.getElementById('edit-form-' + studentId);
                editForm.style.display = editForm.style.display === 'none' ? '' : 'none'; // Toggle display
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
