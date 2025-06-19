<?php
$host = 'localhost'; // أو عنوان السيرفر الخاص بك
$username = 'root'; // اسم المستخدم لقاعدة البيانات
$password = ''; // كلمة مرور قاعدة البيانات
$database = 'school'; // اسم قاعدة البيانات

// إنشاء اتصال
$conn = new mysqli($host, $username, $password, $database);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("خطأ في الاتصال: " . $conn->connect_error);
}

// تعيين مجموعة الأحرف إلى UTF-8 لدعم اللغة العربية
$conn->set_charset("utf8");

?>
