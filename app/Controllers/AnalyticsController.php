<?php

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/Analytics.php';
require_once __DIR__ . '/../../middleware/Auth.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AnalyticsController extends Controller {

    public function dashboard(): void {
        Auth::requireAdmin();

        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        $summary = Analytics::getSummary($startDate, $endDate);
        $pageViewStats = Analytics::getPageViewStats($startDate, $endDate);
        $topPages = Analytics::getTopPages(10, $startDate, $endDate);
        $deviceStats = Analytics::getDeviceStats($startDate, $endDate);
        $browserStats = Analytics::getBrowserStats($startDate, $endDate);
        $osStats = Analytics::getOsStats($startDate, $endDate);
        $referrerStats = Analytics::getReferrerStats(10, $startDate, $endDate);
        $hourlyStats = Analytics::getHourlyStats($startDate, $endDate);
        $activeUsers = Analytics::getActiveUsers(5);

        $this->render('Analytics/dashboard', [
            'summary' => $summary,
            'pageViewStats' => $pageViewStats,
            'topPages' => $topPages,
            'deviceStats' => $deviceStats,
            'browserStats' => $browserStats,
            'osStats' => $osStats,
            'referrerStats' => $referrerStats,
            'hourlyStats' => $hourlyStats,
            'activeUsers' => $activeUsers,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    public function exportReport(): void {
        Auth::requireAdmin();

        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $format = $_GET['format'] ?? 'csv';

        $summary = Analytics::getSummary($startDate, $endDate);
        $pageViewStats = Analytics::getPageViewStats($startDate, $endDate);
        $topPages = Analytics::getTopPages(50, $startDate, $endDate);
        $deviceStats = Analytics::getDeviceStats($startDate, $endDate);
        $browserStats = Analytics::getBrowserStats($startDate, $endDate);
        $osStats = Analytics::getOsStats($startDate, $endDate);
        $referrerStats = Analytics::getReferrerStats(50, $startDate, $endDate);
        $hourlyStats = Analytics::getHourlyStats($startDate, $endDate);

        $filename = 'analytics_report_' . $startDate . '_to_' . $endDate;
        $data = compact('summary', 'pageViewStats', 'topPages', 'deviceStats', 'browserStats', 'osStats', 'referrerStats', 'hourlyStats', 'startDate', 'endDate');

        switch ($format) {
            case 'pdf':
                $this->exportPDF($filename, $data);
                break;
            default:
                $this->exportExcel($filename, $data);
        }
    }

    private function exportExcel(string $filename, array $data): void {
        extract($data);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sumar');

        // Header styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ];

        // Title
        $sheet->setCellValue('A1', 'RAPORT ANALITICĂ WEBSITE');
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->setCellValue('A2', 'Perioada: ' . $startDate . ' până la ' . $endDate);
        $sheet->setCellValue('A3', 'Generat la: ' . date('Y-m-d H:i:s'));

        // Summary
        $sheet->setCellValue('A5', 'SUMAR');
        $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(12);
        $sheet->setCellValue('A6', 'Metric')->setCellValue('B6', 'Valoare');
        $sheet->getStyle('A6:B6')->applyFromArray($headerStyle);
        $sheet->setCellValue('A7', 'Total vizualizări')->setCellValue('B7', $summary['total_views'] ?? 0);
        $sheet->setCellValue('A8', 'Sesiuni unice')->setCellValue('B8', $summary['unique_sessions'] ?? 0);
        $sheet->setCellValue('A9', 'Utilizatori autentificați')->setCellValue('B9', $summary['unique_users'] ?? 0);
        $sheet->setCellValue('A10', 'Zile monitorizate')->setCellValue('B10', $summary['days_tracked'] ?? 0);

        // Daily Stats Sheet
        $dailySheet = $spreadsheet->createSheet();
        $dailySheet->setTitle('Vizualizări zilnice');
        $dailySheet->setCellValue('A1', 'Data')->setCellValue('B1', 'Vizualizări')->setCellValue('C1', 'Sesiuni')->setCellValue('D1', 'Utilizatori logați');
        $dailySheet->getStyle('A1:D1')->applyFromArray($headerStyle);
        $row = 2;
        foreach ($pageViewStats as $stat) {
            $dailySheet->setCellValue('A'.$row, $stat['date'])
                       ->setCellValue('B'.$row, $stat['total_views'])
                       ->setCellValue('C'.$row, $stat['unique_sessions'])
                       ->setCellValue('D'.$row, $stat['logged_in_users']);
            $row++;
        }

        // Top Pages Sheet
        $pagesSheet = $spreadsheet->createSheet();
        $pagesSheet->setTitle('Pagini populare');
        $pagesSheet->setCellValue('A1', 'Pagină')->setCellValue('B1', 'Vizualizări')->setCellValue('C1', 'Vizitatori unici');
        $pagesSheet->getStyle('A1:C1')->applyFromArray($headerStyle);
        $row = 2;
        foreach ($topPages as $page) {
            $pagesSheet->setCellValue('A'.$row, $page['page_url'])
                       ->setCellValue('B'.$row, $page['views'])
                       ->setCellValue('C'.$row, $page['unique_visitors']);
            $row++;
        }

        // Devices Sheet
        $devicesSheet = $spreadsheet->createSheet();
        $devicesSheet->setTitle('Dispozitive');
        $devicesSheet->setCellValue('A1', 'Dispozitiv')->setCellValue('B1', 'Vizualizări')->setCellValue('C1', 'Sesiuni');
        $devicesSheet->getStyle('A1:C1')->applyFromArray($headerStyle);
        $row = 2;
        foreach ($deviceStats as $device) {
            $devicesSheet->setCellValue('A'.$row, $device['device_type'])
                         ->setCellValue('B'.$row, $device['views'])
                         ->setCellValue('C'.$row, $device['sessions']);
            $row++;
        }

        // Browsers Sheet
        $browsersSheet = $spreadsheet->createSheet();
        $browsersSheet->setTitle('Browsere');
        $browsersSheet->setCellValue('A1', 'Browser')->setCellValue('B1', 'Vizualizări');
        $browsersSheet->getStyle('A1:B1')->applyFromArray($headerStyle);
        $row = 2;
        foreach ($browserStats as $browser) {
            $browsersSheet->setCellValue('A'.$row, $browser['browser'] ?? 'Necunoscut')
                          ->setCellValue('B'.$row, $browser['views']);
            $row++;
        }

        // Auto-size columns
        foreach ($spreadsheet->getAllSheets() as $s) {
            foreach (range('A', 'D') as $col) {
                $s->getColumnDimension($col)->setAutoSize(true);
            }
        }

        $spreadsheet->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private function exportPDF(string $filename, array $data): void {
        extract($data);
        
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        $pdf->SetCreator('Casa de Producție');
        $pdf->SetAuthor('Admin');
        $pdf->SetTitle('Raport Analitică Website');
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->SetFont('dejavusans', '', 10);
        
        $pdf->AddPage();

        // Title
        $pdf->SetFont('dejavusans', 'B', 18);
        $pdf->SetTextColor(68, 114, 196);
        $pdf->Cell(0, 10, 'RAPORT ANALITICĂ WEBSITE', 0, 1, 'C');
        
        $pdf->SetFont('dejavusans', 'I', 10);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 6, 'Perioada: ' . $startDate . ' până la ' . $endDate, 0, 1, 'C');
        $pdf->Cell(0, 6, 'Generat la: ' . date('Y-m-d H:i:s'), 0, 1, 'C');
        $pdf->Ln(8);

        // Summary
        $pdf->SetFont('dejavusans', 'B', 14);
        $pdf->SetTextColor(68, 114, 196);
        $pdf->Cell(0, 8, 'SUMAR', 0, 1);
        $pdf->SetTextColor(0, 0, 0);
        
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->SetFillColor(68, 114, 196);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(90, 7, 'Metric', 1, 0, 'C', true);
        $pdf->Cell(90, 7, 'Valoare', 1, 1, 'C', true);
        
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(240, 240, 240);
        $fill = false;
        
        $summaryData = [
            ['Total vizualizări', number_format($summary['total_views'] ?? 0)],
            ['Sesiuni unice', number_format($summary['unique_sessions'] ?? 0)],
            ['Utilizatori autentificați', number_format($summary['unique_users'] ?? 0)],
            ['Zile monitorizate', $summary['days_tracked'] ?? 0]
        ];
        
        foreach ($summaryData as $row) {
            $pdf->Cell(90, 6, $row[0], 1, 0, 'L', $fill);
            $pdf->Cell(90, 6, $row[1], 1, 1, 'R', $fill);
            $fill = !$fill;
        }
        $pdf->Ln(8);

        // Top Pages
        $pdf->SetFont('dejavusans', 'B', 14);
        $pdf->SetTextColor(68, 114, 196);
        $pdf->Cell(0, 8, 'PAGINI POPULARE', 0, 1);
        $pdf->SetTextColor(0, 0, 0);
        
        $pdf->SetFont('dejavusans', '', 9);
        $pdf->SetFillColor(68, 114, 196);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(110, 7, 'Pagină', 1, 0, 'C', true);
        $pdf->Cell(35, 7, 'Vizualizări', 1, 0, 'C', true);
        $pdf->Cell(35, 7, 'Unici', 1, 1, 'C', true);
        
        $pdf->SetTextColor(0, 0, 0);
        $fill = false;
        foreach (array_slice($topPages, 0, 15) as $page) {
            $pdf->Cell(110, 6, substr($page['page_url'], 0, 60), 1, 0, 'L', $fill);
            $pdf->Cell(35, 6, number_format($page['views']), 1, 0, 'R', $fill);
            $pdf->Cell(35, 6, number_format($page['unique_visitors']), 1, 1, 'R', $fill);
            $fill = !$fill;
        }
        $pdf->Ln(8);

        // Devices
        $pdf->SetFont('dejavusans', 'B', 14);
        $pdf->SetTextColor(68, 114, 196);
        $pdf->Cell(0, 8, 'DISPOZITIVE', 0, 1);
        $pdf->SetTextColor(0, 0, 0);
        
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->SetFillColor(68, 114, 196);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(90, 7, 'Dispozitiv', 1, 0, 'C', true);
        $pdf->Cell(90, 7, 'Vizualizări', 1, 1, 'C', true);
        
        $pdf->SetTextColor(0, 0, 0);
        $fill = false;
        foreach ($deviceStats as $device) {
            $pdf->Cell(90, 6, ucfirst($device['device_type']), 1, 0, 'L', $fill);
            $pdf->Cell(90, 6, number_format($device['views']), 1, 1, 'R', $fill);
            $fill = !$fill;
        }
        $pdf->Ln(8);

        // Browsers
        $pdf->SetFont('dejavusans', 'B', 14);
        $pdf->SetTextColor(68, 114, 196);
        $pdf->Cell(0, 8, 'BROWSERE', 0, 1);
        $pdf->SetTextColor(0, 0, 0);
        
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->SetFillColor(68, 114, 196);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(90, 7, 'Browser', 1, 0, 'C', true);
        $pdf->Cell(90, 7, 'Vizualizări', 1, 1, 'C', true);
        
        $pdf->SetTextColor(0, 0, 0);
        $fill = false;
        foreach ($browserStats as $browser) {
            $pdf->Cell(90, 6, $browser['browser'] ?? 'Necunoscut', 1, 0, 'L', $fill);
            $pdf->Cell(90, 6, number_format($browser['views']), 1, 1, 'R', $fill);
            $fill = !$fill;
        }

        $pdf->Output($filename . '.pdf', 'D');
        exit;
    }
}
