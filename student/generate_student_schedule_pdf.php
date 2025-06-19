<?php
session_start();
include 'db.php';
require_once('../TCPDF-main/tcpdf.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['student_id'])) {
    die("Error: Student ID not found in the session. Please log in.");
}

// --- Fetch student info ---
$sqlStudentData = "SELECT name, semester, class_number FROM students WHERE id = ?";
$stmt = $conn->prepare($sqlStudentData);
$stmt->bind_param("i", $_SESSION['student_id']);
$stmt->execute();
$studentData = $stmt->get_result()->fetch_assoc();
$stmt->close();

// --- Fetch current (incomplete) courses ---
$sqlSchedule = "SELECT c.name AS course_name, sc.day, sc.time 
                FROM student_courses sc 
                JOIN courses c ON sc.course_id = c.id 
                WHERE sc.student_id = ? AND sc.completed = 0";
$stmt = $conn->prepare($sqlSchedule);
$stmt->bind_param("i", $_SESSION['student_id']);
$stmt->execute();
$resultSchedule = $stmt->get_result();
$courses = $resultSchedule->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// --- Initialize PDF ---
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// RTL + language settings
$pdf->setRTL(true);
$lg = [
    'a_meta_charset' => 'UTF-8',
    'a_meta_dir'     => 'rtl',
    'a_meta_language'=> 'ar',
];
$pdf->setLanguageArray($lg);

// Disable auto page-break to keep it all on one page
$pdf->SetAutoPageBreak(false, /* bottom margin */ 15);

// Margins: left, top, right
$pdf->SetMargins(10, 20, 10);
$pdf->AddPage();

// --- Header ---
$pdf->SetFont('dejavusans', 'B', 16);
$pdf->Cell(0, 10, 'جدول الطالب', 0, 1, 'C', 0, '', 0, false, 'R', 'M');
// draw a thin line below the title
$pdf->SetLineStyle(['width'=>0.3, 'color'=>[100,100,100]]);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(5);

// --- Student Info ---
$pdf->SetFont('dejavusans', '', 11);
$info = [
    'اسم الطالب: '   . htmlspecialchars($studentData['name']),
    'رقم القيد: '     . htmlspecialchars($_SESSION['student_id']),
    'الفصل الدراسي: ' . htmlspecialchars($studentData['semester']),
];
if (!empty($studentData['class_number'])) {
    $info[] = 'رقم الفصل: ' . htmlspecialchars($studentData['class_number']);
}
foreach ($info as $line) {
    $pdf->Cell(0, 7, $line, 0, 1, 'R');
}
$pdf->Ln(3);

// --- Table Header ---
$pdf->SetFillColor(225, 235, 255);
$pdf->SetFont('dejavusans', 'B', 12);
$pdf->Cell(80, 9, 'اسم المقرر', 1, 0, 'C', 1);
$pdf->Cell(50, 9, 'اليوم', 1, 0, 'C', 1);
$pdf->Cell(40, 9, 'الوقت', 1, 1, 'C', 1);

// --- Table Content ---
$pdf->SetFont('dejavusans', '', 10);
$fill = false;
if (count($courses)) {
    foreach ($courses as $row) {
        $pdf->SetFillColor($fill ? 245 : 255);
        $pdf->Cell(80, 8, htmlspecialchars($row['course_name']), 1, 0, 'R', 1);
        $pdf->Cell(50, 8, htmlspecialchars($row['day']), 1, 0, 'R', 1);
        $pdf->Cell(40, 8, htmlspecialchars($row['time']), 1, 1, 'R', 1);
        $fill = !$fill;
    }
} else {
    $pdf->Cell(170, 8, 'لا توجد مقررات مسجلة حالياً', 1, 1, 'C', 1);
}

// --- Footer (fixed Arabic rendering) ---
$pdf->SetY(-20);
$pdf->SetFont('dejavusans', 'I', 8);
$pdf->Cell(0, 5, '' . date('Y/m/d'), 0, 1, 'C');

// Make the “WhySoTech” link clickable in Arabic
$footerHtml = '<p align="center" dir="rtl" style="font-family:dejavusans; font-size:8pt;">
 <a href="mailto:whysot.tech@gmail.com">WhySoTech</a>
</p>';
$pdf->writeHTMLCell(0, 0, 10, null, $footerHtml, 0, 1, false, true, 'C', true);

// --- Output PDF ---
$pdf->Output('student_schedule.pdf', 'I');

