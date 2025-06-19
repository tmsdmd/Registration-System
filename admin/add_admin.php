<?php
session_start();
include 'db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['username'])) {
    header("Location:/Login/admin/dashboard.php");
    exit();
}

// Handle adding a new admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_admin'])) {
    $username = $_POST['username'];
    $password = $_POST['password']; // Store as plain text

    // Check if the username already exists
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM admins WHERE username = ?");
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        echo "<div class='alert alert-danger'>هذا اسم المستخدم موجود بالفعل!</div>";
    } else {
        // Insert new admin
        $stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $password);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>تم إضافة المسؤول بنجاح!</div>";
        } else {
            echo "<div class='alert alert-danger'>حدث خطأ أثناء إضافة المسؤول: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}

// Handle deletion of an admin
if (isset($_GET['delete'])) {
    $admin_id = $_GET['delete'];
    $deleteStmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
    $deleteStmt->bind_param("i", $admin_id);
    if ($deleteStmt->execute()) {
        echo "<div class='alert alert-success'>تم حذف المسؤول بنجاح!</div>";
    } else {
        echo "<div class='alert alert-danger'>حدث خطأ أثناء حذف المسؤول: " . $deleteStmt->error . "</div>";
    }
    $deleteStmt->close();
}

// Fetch all admins
$result = $conn->query("SELECT * FROM admins");
$admins = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة مسؤول</title>
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
        <h2>إضافة مسؤول</h2>

        <!-- Add Admin Form -->
        <div class="mb-4">
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="username">اسم المستخدم</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="password">كلمة المرور</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
                <button type="submit" name="add_admin" class="btn btn-primary">حفظ</button>
            </form>
        </div>

        <!-- Admin List Table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم المستخدم</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $index => $admin): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($admin['username']) ?></td>
                        <td>
                            <a href="?delete=<?= htmlspecialchars($admin['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد أنك تريد حذف هذا المسؤول؟');">حذف</a>
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
    
    
</body>
</html>

<?php
$conn->close();
?>
