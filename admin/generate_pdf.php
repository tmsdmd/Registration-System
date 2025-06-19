<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../TCPDF-main/tcpdf.php');
include 'db.php';

// Fetch schedule data
$schedule = [];
$sql = "SELECT cs.day, cs.time, c.name AS course_name 
        FROM course_schedule cs 
        JOIN courses c ON cs.course_id = c.id
        ORDER BY FIELD(cs.day, 'السبت', 'الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'), 
                 cs.time";
$result = $conn->query($sql);

if (!$result) {
    die("Database query error: " . $conn->error);
}

while ($row = $result->fetch_assoc()) {
    $schedule[$row['day']][$row['time']][] = $row['course_name'];
}
$conn->close();

// Create PDF document (Landscape)
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();

// Set font (supports Arabic)
$pdf->SetFont('dejavusans', '', 10);

// Color scheme
$headerColor = [58, 83, 155];   // Dark blue
$primaryColor = [71, 122, 173]; // Blue
$secondaryColor = [220, 230, 241]; // Light blue
$textColor = [50, 50, 50];      // Dark gray
$footerColor = [100, 100, 100]; // Gray for footer

// Main title
$pdf->SetTextColor($headerColor[0], $headerColor[1], $headerColor[2]);
$pdf->SetFont('dejavusans', 'B', 18);
$pdf->Cell(0, 12, 'الجدول الدراسي', 0, 1, 'C');
$pdf->SetFont('dejavusans', 'B', 14);
$pdf->Cell(0, 10, 'كلية التربية - قسم الحاسوب', 0, 1, 'C');
$pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
$pdf->SetFont('dejavusans', '', 12);
$pdf->Cell(0, 10, 'تم الإنشاء في: ' . date('Y-m-d H:i'), 0, 0, 'C');
$pdf->Ln(15);

// Days and time slots
$days = ['السبت', 'الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'];
$time_slots = [
    '08:00:00' => '08:00-10:00',
    '10:00:00' => '10:00-12:00',
    '12:00:00' => '12:00-14:00',
    '14:00:00' => '14:00-16:00',
    '16:00:00' => '16:00-18:00'
];

// Table dimensions
$time_col_width = 20;
$day_col_width = 42;
$row_height = 14;

// Table header
$pdf->SetFillColor($headerColor[0], $headerColor[1], $headerColor[2]);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('dejavusans', 'B', 10);
$pdf->Cell($time_col_width, $row_height, 'الوقت', 1, 0, 'C', 1);

foreach ($days as $day) {
    $pdf->Cell($day_col_width, $row_height, $day, 1, 0, 'C', 1);
}
$pdf->Ln();

// Table content
$pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
$pdf->SetFont('dejavusans', '', 9);

$row_count = 0;
foreach ($time_slots as $time_key => $time_display) {
    $fill_color = ($row_count % 2) ? $secondaryColor : [255, 255, 255];
    $pdf->SetFillColor($fill_color[0], $fill_color[1], $fill_color[2]);
    $row_count++;
    
    $pdf->Cell($time_col_width, $row_height, $time_display, 1, 0, 'C', true);
    
    foreach ($days as $day) {
        $content = '';
        if (!empty($schedule[$day][$time_key])) {
            $content = implode("\n", $schedule[$day][$time_key]);
        }
        
        $pdf->MultiCell($day_col_width, $row_height, $content, 1, 'C', true, 0, '', '', true, 0, false, true, $row_height, 'M');
    }
    $pdf->Ln();
}



$pdf->Cell(0, 10, 'بواسطة WhySoTech', 0, 0, 'C');   

// Output the PDF
$pdf->Output('الجدول الدراسي.pdf', 'I');
?>
