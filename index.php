<?php
session_start();
include 'db.php'; // Ensure database connection is correct
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام تسجيل الدخول</title>
    <link rel="stylesheet" href="/Login/assets/css/bootstrap.min.css">
<link rel="stylesheet" href="/Login/assets/css/all.min.css">
<link rel="stylesheet" href="/Login/assets/css/select2.min.css">
    <link rel="stylesheet" href="/Login/CSS/styles2.css">
    
</head>
<body>
<nav class="navbar navbar-light bg-light">
    <a class="navbar-brand" href="https://uot.edu.ly/edg/cs/">قسم الحاسوب - كلية التربية قصر بن غشير</a>
</nav>

<div class="welcome-container">


    
    <div class="d-flex flex-column flex-md-row justify-content-center">
        <a href="/Login/admin/admin_login.php" class="btn btn-primary btn-custom btn-admin">
             المسؤولين
        </a>
        <a href="/Login/student/student.php" class="btn btn-primary btn-custom btn-student">
             الطلاب
        </a>
    </div>
    
    <div class="mt-4"dir="rtl">
        <p>للمساعدة، يرجى التواصل مع الدعم الفني</p> 
         
        
    تم التطوير بواسطة <a href="mailto:whysot.tech@gmail.com" title="Contact WhySoTech">WhySoTech</a>

    </div>
</div>

<!-- JS -->
<script src="/Login/assets/js/jquery-3.5.1.min.js"></script>
<script src="/Login/assets/js/popper.min.js"></script>
<script src="/Login/assets/js/bootstrap.min.js"></script>
<script src="/Login/assets/js/select2.min.js"></script>
</body>
</html>
