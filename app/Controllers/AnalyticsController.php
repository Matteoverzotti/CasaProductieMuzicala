<?php

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/Analytics.php';
require_once __DIR__ . '/../../middleware/Auth.php';

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
}
