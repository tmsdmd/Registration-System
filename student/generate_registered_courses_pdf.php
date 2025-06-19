<?php
session_start();
include 'db.php';
require_once('../TCPDF-main/tcpdf.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['student_id'])) {
    die("Error: Student ID not found in the session. Please log in.");
}

// --- استرجاع بيانات الطالب ---
$sqlStudentData = "SELECT name, semester, class_number FROM students WHERE id = ?";
$stmt = $conn->prepare($sqlStudentData);
$stmt->bind_param("i", $_SESSION['student_id']);
$stmt->execute();
$studentData = $stmt->get_result()->fetch_assoc();
$stmt->close();

// --- استرجاع المقررات المسجلة الحالية ---
$sqlStudentCourses = "SELECT c.id, c.name, c.units
                     FROM student_courses sc
                     JOIN courses c ON sc.course_id = c.id
                     WHERE sc.student_id = ? AND sc.completed = 0";
$stmt = $conn->prepare($sqlStudentCourses);
$stmt->bind_param("i", $_SESSION['student_id']);
$stmt->execute();
$resultStudentCourses = $stmt->get_result();
$studentCourses = [];
$totalUnits = 0;
while ($row = $resultStudentCourses->fetch_assoc()) {
    $studentCourses[] = $row;
    $totalUnits += $row['units'];
}
$stmt->close();
$conn->close();

// --- إنشاء مستند PDF صفحة واحدة بتصميم بسيط وواضح بدون صورة ---
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->setRTL(true);
$pdf->setLanguageArray([
    'a_meta_charset' => 'UTF-8',
    'a_meta_dir'     => 'rtl',
    'a_meta_language'=> 'ar',
]);

$pdf->SetMargins(15, 20, 15);
$pdf->SetAutoPageBreak(false, 20);
$pdf->AddPage();

// --- العنوان الرئيسي مع خط تحت ---
$pdf->SetFont('dejavusans', 'B', 16);
$pdf->SetTextColor(0);
$pdf->Cell(0, 10, 'المقررات المسجلة', 0, 1, 'C');
$pdf->SetLineWidth(0.4);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());

// --- معلومات الطالب ---
$pdf->Ln(5);
$pdf->SetFont('dejavusans', '', 12);
$infoTable = '<table border="0" cellpadding="4" dir="rtl" style="font-family:dejavusans;font-size:12pt;">
<tr>
<td width="33%"><strong>الاسم:</strong><br>' . htmlspecialchars($studentData['name']) . '</td>
<td width="33%"><strong>الرقم الجامعي:</strong><br>' . htmlspecialchars($_SESSION['student_id']) . '</td>
<td width="33%"><strong>الفصل:</strong><br>' . htmlspecialchars($studentData['semester']) . '</td>
</tr>
</table>';
$pdf->writeHTMLCell(0, 0, 15, null, $infoTable, 0, 1, false, true, 'R', true);

// --- جدول المقررات بسيط وواضح ---
$pdf->Ln(5);
$pdf->SetFont('dejavusans', 'B', 12);
$pdf->SetFillColor(230, 230, 230);
$pdf->SetTextColor(0);
$headers = ['#', 'اسم المقرر', 'رمز المقرر', 'الوحدات'];
$widths = [15, 100, 40, 30];
foreach ($headers as $i => $h) {
    $pdf->Cell($widths[$i], 8, $h, 1, 0, 'C', 1);
}
$pdf->Ln();
$pdf->SetFont('dejavusans', '', 11);

if (empty($studentCourses)) {
    $pdf->Cell(array_sum($widths), 8, 'لا توجد مقررات مسجلة حالياً', 1, 1, 'C');
} else {
    foreach ($studentCourses as $i => $course) {
        $fill = $i % 2 == 0;
        $pdf->SetFillColor($fill ? 245 : 255);
        $pdf->Cell($widths[0], 8, $i+1, 'LR', 0, 'C', 1);
        $pdf->Cell($widths[1], 8, htmlspecialchars($course['name']), 'LR', 0, 'R', 1);
        $pdf->Cell($widths[2], 8, htmlspecialchars($course['id']), 'LR', 0, 'C', 1);
        $pdf->Cell($widths[3], 8, htmlspecialchars($course['units']), 'LR', 1, 'C', 1);
    }
    // border bottom
    $pdf->Cell(array_sum($widths), 0, '', 'T');
}

// --- المجموع الكلي للوحدات ---
$pdf->Ln(8);
$pdf->SetFont('dejavusans', 'B', 12);
$pdf->Cell(130);
$pdf->Cell(40, 8, 'المجموع الكلي للوحدات:', 0, 0, 'R');
$pdf->Cell(25, 8, $totalUnits, 0, 1, 'C');

// --- التذييل بسيط ---
$pdf->SetY(-25);
$pdf->SetLineWidth(0.3);
$pdf->Line(15, 275, 195, 275);
$pdf->SetFont('dejavusans', 'I', 8);
$pdf->Cell(0, 5, ' ' . date('Y/m/d'), 0, 1, 'C');
$pdf->Cell(0, 5, 'Developed by WhySoTech', 0, 1, 'C');

$pdf->Output('registered_courses.pdf', 'I');
?>

