# cs-course-registration-system-Arabic
# نظام تسجيل مقررات قسم الحاسوب

> **ملاحظة:** هذا النظام مخصص للغة العربية وواجهة الاستخدام بالكامل باللغة العربية.

## نظرة عامة

هذا النظام يتيح لطلاب قسم الحاسوب تسجيل المقررات الدراسية إلكترونيًا ومتابعة حالة تسجيلهم. يوفر النظام واجهة سهلة الاستخدام لكل من الطلاب والإدارة، ويساعد في تنظيم عمليات التسجيل بشكل فعال.

## المميزات

- تسجيل دخول للطلاب والإدارة
- إمكانية تسجيل المقررات إلكترونيًا
- عرض المقررات المسجلة والوحدات
- تصدير كشف المقررات PDF
- نظام إدارة للمستخدمين والمقررات
- حماية البيانات وجلسات المستخدمين

## متطلبات التشغيل

- PHP 7.2 أو أحدث
- MySQL أو MariaDB
- خادم Apache أو Nginx
- Composer (اختياري لبعض الإضافات)
- [TCPDF](https://tcpdf.org) لتوليد ملفات PDF

## طريقة التشغيل

1. استنسخ المشروع:
   ```bash
   git clone https://github.com/username/cs-course-registration-system.git
   ```
2. أنشئ قاعدة البيانات واستورد الجداول من ملف `database`.
3. حدّث بيانات الاتصال بقاعدة البيانات في ملف `db.php`.
4. تأكد من رفع مجلد المشروع على السيرفر وتهيئة الصلاحيات.
5. سجل الدخول بحساب مسؤول أو طالب وابدأ تجربة النظام.

## المجلدات الرئيسية

- `admin/` صفحات المشرف وإدارة النظام
- `student/` صفحات الطلاب
- `assets/` ملفات CSS وJS والخطوط
- `database/` ملفات SQL
- `img/` الصور والشعارات
- `TCPDF-main/` مكتبة إنشاء PDF

## المساهمة

للمساهمة، يرجى عمل Fork للمستودع ثم إرسال Pull Request بعد إجراء التعديلات.

## الرخصة

هذا المشروع مجاني ومفتوح المصدر للاستخدام الأكاديمي.

---

# Computer Science Course Registration System

> **Note:** This system is primarily designed for Arabic language users and the user interface is fully in Arabic.

## Overview

This system enables Computer Science students to register for courses online and track their registration status. The platform provides an easy-to-use interface for both students and administrators, helping to streamline the registration process.

## Features

- Student and admin login
- Online course registration
- View registered courses and units
- Export registered courses as PDF
- User and course management
- Data and session protection

## Requirements

- PHP 7.2 or later
- MySQL or MariaDB
- Apache or Nginx web server
- Composer (optional for some extensions)
- [TCPDF](https://tcpdf.org) for PDF generation

## Getting Started

1. Clone the repository:
   ```bash
   git clone https://github.com/username/cs-course-registration-system.git
   ```
2. Create the database and import tables from the `database` directory.
3. Update your database credentials in `db.php`.
4. Upload the project folder to your server and set the required permissions.
5. Log in as admin or student to start using the system.

## Main Directories

- `admin/` Admin pages and system management
- `student/` Student pages
- `assets/` CSS, JS, and fonts
- `database/` SQL files
- `img/` Images and logos
- `TCPDF-main/` PDF generation library

## Contribution

To contribute, please fork the repository and submit a pull request with your changes.

## License

This project is free and open source for academic use.
