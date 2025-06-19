<?php
session_start();
include 'db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// التحقق من تسجيل الدخول
if (!isset($_SESSION['username'])) {
    header("Location:/Login/admin/dashboard.php");
    exit();
}

// معالجة إضافة مقرر جديد
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_course'])) {
    $course_id = htmlspecialchars($_POST['course_id']);
    $course_name = htmlspecialchars($_POST['course_name']);
    $status = htmlspecialchars($_POST['status']);
    $units = (int)$_POST['units'];
    $requirements = htmlspecialchars($_POST['requirements']);

    // التحقق من عدم وجود رمز المقرر مسبقاً
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM courses WHERE id = ?");
    $checkStmt->bind_param("s", $course_id);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        echo "<div class='alert alert-danger'>هذا المعرف موجود بالفعل!</div>";
    } else {
        // إدراج المقرر الجديد
        $stmt = $conn->prepare("INSERT INTO courses (id, name, status, units) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $course_id, $course_name, $status, $units);
        if ($stmt->execute()) {
            // إضافة المتطلبات إذا وجدت
            if (!empty($requirements)) {
                $reqStmt = $conn->prepare("INSERT INTO Requirements (course_id, Requirements_id) VALUES (?, ?)");
                $reqStmt->bind_param("ss", $course_id, $requirements);
                if (!$reqStmt->execute()) {
                    echo "<div class='alert alert-danger'>حدث خطأ أثناء إضافة المتطلبات: " . $reqStmt->error . "</div>";
                }
                $reqStmt->close();
            }
            echo "<div class='alert alert-success'>تم إضافة المقرر بنجاح!</div>";
        } else {
            echo "<div class='alert alert-danger'>حدث خطأ أثناء إضافة المقرر: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}

// معالجة تعديل مقرر
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_course'])) {
    $course_id = htmlspecialchars($_POST['course_id']);
    $course_name = htmlspecialchars($_POST['course_name']);
    $status = htmlspecialchars($_POST['status']);
    $units = (int)$_POST['units'];
    $requirements = htmlspecialchars($_POST['requirements']);

    // تحديث بيانات المقرر
    $updateStmt = $conn->prepare("UPDATE courses SET name = ?, status = ?, units = ? WHERE id = ?");
    $updateStmt->bind_param("ssis", $course_name, $status, $units, $course_id);
    if ($updateStmt->execute()) {
        // تحديث المتطلبات
        if (!empty($requirements)) {
            $reqStmt = $conn->prepare("REPLACE INTO Requirements (course_id, Requirements_id) VALUES (?, ?)");
            $reqStmt->bind_param("ss", $course_id, $requirements);
            if (!$reqStmt->execute()) {
                echo "<div class='alert alert-danger'>حدث خطأ أثناء تحديث المتطلبات: " . $reqStmt->error . "</div>";
            }
            $reqStmt->close();
        } else {
            // حذف المتطلبات إذا لم يتم تقديم أي متطلبات
            $deleteReqStmt = $conn->prepare("DELETE FROM Requirements WHERE course_id = ?");
            $deleteReqStmt->bind_param("s", $course_id);
            $deleteReqStmt->execute();
            $deleteReqStmt->close();
        }
        echo "<div class='alert alert-success'>تم تعديل المقرر بنجاح!</div>";
    } else {
        echo "<div class='alert alert-danger'>حدث خطأ أثناء تعديل المقرر: " . $updateStmt->error . "</div>";
    }
    $updateStmt->close();
}

// معالجة الإجراءات الجماعية
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bulk_action'])) {
    if (!empty($_POST['selected_courses'])) {
        $selected_courses = $_POST['selected_courses'];
        $success_count = 0;

        if ($_POST['bulk_action'] == 'delete') {
            // حذف المقررات المحددة
            foreach ($selected_courses as $course_id) {
                $course_id = htmlspecialchars($course_id, ENT_QUOTES, 'UTF-8');

                // حذف المتطلبات أولاً
                $deleteRequirementsStmt = $conn->prepare("DELETE FROM Requirements WHERE course_id = ?");
                $deleteRequirementsStmt->bind_param("s", $course_id);
                $deleteRequirementsStmt->execute();
                $deleteRequirementsStmt->close();

                // حذف الجدول الزمني
                $deleteScheduleStmt = $conn->prepare("DELETE FROM course_schedule WHERE course_id = ?");
                $deleteScheduleStmt->bind_param("s", $course_id);
                $deleteScheduleStmt->execute();
                $deleteScheduleStmt->close();

                // حذف المقرر
                $deleteStmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
                $deleteStmt->bind_param("s", $course_id);
                if ($deleteStmt->execute()) {
                    $success_count++;
                }
                $deleteStmt->close();
            }
            echo "<div class='alert alert-success'>تم حذف $success_count من " . count($selected_courses) . " مقررات بنجاح!</div>";
        }
        elseif ($_POST['bulk_action'] == 'open' || $_POST['bulk_action'] == 'close') {
            // فتح أو إغلاق المقررات المحددة
            $new_status = $_POST['bulk_action'] == 'open' ? 'open' : 'closed';

            foreach ($selected_courses as $course_id) {
                $course_id = htmlspecialchars($course_id, ENT_QUOTES, 'UTF-8');

                $updateStmt = $conn->prepare("UPDATE courses SET status = ? WHERE id = ?");
                $updateStmt->bind_param("ss", $new_status, $course_id);
                if ($updateStmt->execute()) {
                    $success_count++;
                }
                $updateStmt->close();
            }

            $status_text = $new_status == 'open' ? 'فتح' : 'إغلاق';
            echo "<div class='alert alert-success'>تم $status_text $success_count من " . count($selected_courses) . " مقررات بنجاح!</div>";
        }
    } else {
        echo "<div class='alert alert-warning'>لم يتم تحديد أي مقررات!</div>";
    }
}

// معالجة فتح/إغلاق جميع المقررات
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_all_status'])) {
    $new_status = $_POST['change_all_status'] == 'open_all' ? 'open' : 'closed';

    $updateAllStmt = $conn->prepare("UPDATE courses SET status = ?");
    $updateAllStmt->bind_param("s", $new_status);
    if ($updateAllStmt->execute()) {
        $status_text = $new_status == 'open' ? 'فتح' : 'إغلاق';
        echo "<div class='alert alert-success'>تم $status_text جميع المقررات بنجاح!</div>";
    } else {
        echo "<div class='alert alert-danger'>حدث خطأ أثناء تحديث حالة المقررات!</div>";
    }
    $updateAllStmt->close();
}

// معالجة تغيير حالة مقرر فردي
if (isset($_GET['toggle_status'])) {
    $course_id = htmlspecialchars($_GET['toggle_status'], ENT_QUOTES, 'UTF-8');
    $current_status = htmlspecialchars($_GET['current_status'], ENT_QUOTES, 'UTF-8');
    $new_status = $current_status == 'open' ? 'closed' : 'open';

    $toggleStmt = $conn->prepare("UPDATE courses SET status = ? WHERE id = ?");
    $toggleStmt->bind_param("ss", $new_status, $course_id);
    if ($toggleStmt->execute()) {
        $status_text = $new_status == 'open' ? 'فتح' : 'إغلاق';
        echo "<div class='alert alert-success'>تم $status_text المقرر بنجاح!</div>";
    } else {
        echo "<div class='alert alert-danger'>حدث خطأ أثناء تغيير حالة المقرر!</div>";
    }
    $toggleStmt->close();
}

// معالجة حذف مقرر فردي
if (isset($_GET['delete'])) {
    $id = htmlspecialchars($_GET['delete'], ENT_QUOTES, 'UTF-8');

    // حذف المتطلبات أولاً
    $deleteRequirementsStmt = $conn->prepare("DELETE FROM Requirements WHERE course_id = ?");
    $deleteRequirementsStmt->bind_param("s", $id);
    $deleteRequirementsStmt->execute();
    $deleteRequirementsStmt->close();

    // حذف الجدول الزمني
    $deleteScheduleStmt = $conn->prepare("DELETE FROM course_schedule WHERE course_id = ?");
    $deleteScheduleStmt->bind_param("s", $id);
    $deleteScheduleStmt->execute();
    $deleteScheduleStmt->close();

    // حذف المقرر
    $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->bind_param("s", $id);
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>تم حذف المقرر بنجاح!</div>";
    } else {
        echo "<div class='alert alert-danger'>حدث خطأ أثناء حذف المقرر: " . htmlspecialchars($stmt->error) . "</div>";
    }
    $stmt->close();
}

// تحديد طريقة ترتيب المقررات
$order_by = isset($_GET['order_by']) ? $_GET['order_by'] : 'id';
$order_dir = isset($_GET['order_dir']) ? $_GET['order_dir'] : 'ASC';

// التحقق من صحة معايير الترتيب
$valid_order_columns = ['id', 'name', 'status', 'units'];
$order_by = in_array($order_by, $valid_order_columns) ? $order_by : 'id';
$order_dir = $order_dir == 'DESC' ? 'DESC' : 'ASC';

// جلب جميع المقررات مع متطلباتها
$result = $conn->query("SELECT c.*, r.Requirements_id FROM courses c LEFT JOIN Requirements r ON c.id = r.course_id ORDER BY $order_by $order_dir");
$courses = $result->fetch_all(MYSQLI_ASSOC);
?>


<!DOCTYPE html>
<html lang="ar" >
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>قائمة المقررات</title>
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
                <a class="nav-link active" href="add_course.php"><i class="fas fa-book"></i> قائمة المقررات</a>
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

    <div class="content" dir="rtl">

        <!-- Add Course Form -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-plus-circle ml-2"></i> إضافة مقرر جديد
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label for="course_id">رمز المقرر</label>
                            <input type="text" class="form-control" id="course_id" name="course_id" maxlength="5" required>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="course_name">اسم المقرر</label>
                            <input type="text" class="form-control" id="course_name" name="course_name" required>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="status">الحالة</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="open">مفتوح</option>
                                <option value="closed">مغلق</option>
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="units">الوحدات</label>
                            <input type="number" class="form-control" id="units" name="units" required min="1">
                        </div>
                        <div class="form-group col-md-2">
                            <label for="requirements">المتطلبات</label>
                            <input type="text" class="form-control" id="requirements" name="requirements" placeholder="رمز المقرر">
                        </div>
                    </div>
                    <button type="submit" name="add_course" class="btn btn-primary">
                        <i class="fas fa-save ml-2"></i> حفظ
                    </button>
                </form>
            </div>
        </div>

        <!-- Bulk Actions -->
        <form method="POST" action="" class="mb-3" id="bulkForm">
            <div class="row mb-3">
                <div class="col-md-4">
                    <select name="bulk_action" class="form-control">
                        <option value="">اختر إجراء جماعي...</option>
                        <option value="delete">حذف المحدد</option>
                        <option value="open">فتح المحدد</option>
                        <option value="close">إغلاق المحدد</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-play ml-2"></i> تنفيذ
                    </button>
                </div>
                <div class="col-md-6 text-left">
                    <button type="submit" name="change_all_status" value="open_all" class="btn btn-success">
                        <i class="fas fa-lock-open ml-2"></i> فتح الكل
                    </button>
                    <button type="submit" name="change_all_status" value="close_all" class="btn btn-warning">
                        <i class="fas fa-lock ml-2"></i> إغلاق الكل
                    </button>
                    <a href="?order_by=name&order_dir=<?= $order_dir == 'ASC' ? 'DESC' : 'ASC' ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-sort-alpha-down ml-2"></i> ترتيب حسب الاسم
                    </a>
                    <a href="?order_by=id&order_dir=<?= $order_dir == 'ASC' ? 'DESC' : 'ASC' ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-sort-numeric-down ml-2"></i> ترتيب حسب الرمز
                    </a>
                </div>
            </div>

            <!-- جدول المقررات -->
            <table class="table table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th width="40"><input type="checkbox" id="selectAll"></th>
                        <th>#</th>
                        <th>رمز</th>
                        <th>اسم المقرر</th>
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $index => $course): ?>
                    <tr>
                        <td><input type="checkbox" name="selected_courses[]" value="<?= htmlspecialchars($course['id']) ?>"></td>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($course['id']) ?></td>
                        <td>
                            <?= htmlspecialchars($course['name']) ?>
                            <?php if(!empty($course['Requirements_id'])): ?>
                                <span class="badge badge-info" data-toggle="tooltip" title="المتطلبات: <?= htmlspecialchars($course['Requirements_id']) ?>">?</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-<?= $course['status'] == 'open' ? 'success' : 'danger' ?>">
                                <?= $course['status'] == 'open' ? 'مفتوح' : 'مغلق' ?>
                            </span>
                        </td>
                        <td>
                            <a href="?delete=<?= htmlspecialchars($course['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد؟');"><i class="fas fa-trash-alt"></i></a>
                            <button type="button" class="btn btn-warning btn-sm" onclick="openEditModal('<?= htmlspecialchars($course['id']) ?>', '<?= htmlspecialchars($course['name']) ?>', '<?= $course['status'] ?>', '<?= htmlspecialchars($course['units']) ?>', '<?= htmlspecialchars($course['Requirements_id']) ?>')"><i class="fas fa-edit"></i></button>
                            <a href="?toggle_status=<?= htmlspecialchars($course['id']) ?>&current_status=<?= $course['status'] ?>" class="btn btn-<?= $course['status'] == 'open' ? 'danger' : 'success' ?> btn-sm"><i class="fas fa-<?= $course['status'] == 'open' ? 'lock' : 'lock-open' ?>"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </form>

        <!-- Modal نافذة التعديل -->
        <div class="modal fade" id="editCourseModal" tabindex="-1" aria-labelledby="editCourseLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="" class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editCourseLabel">تعديل مقرر</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="إغلاق">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="course_id" id="modal_course_id">
                        <div class="form-group">
                            <label>اسم المقرر</label>
                            <input type="text" class="form-control" name="course_name" id="modal_course_name" required>
                        </div>
                        <div class="form-group">
                            <label>الحالة</label>
                            <select class="form-control" name="status" id="modal_status" required>
                                <option value="open">مفتوح</option>
                                <option value="closed">مغلق</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>الوحدات</label>
                            <input type="number" class="form-control" name="units" id="modal_units" required min="1">
                        </div>
                        <div class="form-group">
                            <label>المتطلبات</label>
                            <input type="text" class="form-control" name="requirements" id="modal_requirements">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="edit_course" class="btn btn-success">تحديث</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- يجب أن تكون سكربتات الجافاسكريبت في نهاية الصفحة -->
  <!-- JS -->
<script src="/Login/assets/js/jquery-3.5.1.min.js"></script>
<script src="/Login/assets/js/popper.min.js"></script>
<script src="/Login/assets/js/bootstrap.min.js"></script>
<script src="/Login/assets/js/select2.min.js"></script>
    
    <script>
    function openEditModal(id, name, status, units, requirements) {
        document.getElementById('modal_course_id').value = id;
        document.getElementById('modal_course_name').value = name;
        document.getElementById('modal_status').value = status;
        document.getElementById('modal_units').value = units;
        document.getElementById('modal_requirements').value = requirements;
        $('#editCourseModal').modal('show');
    }
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();

        // Select/deselect all
        $('#selectAll').on('change', function() {
            $('input[name="selected_courses[]"]').prop('checked', this.checked);
        });
    });
    </script>
</body>
</html>
<?php
$conn->close();
?>
