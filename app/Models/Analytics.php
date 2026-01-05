<?php

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/../Database.php';

class Analytics extends Model {

    public static function trackPageView(
        string $sessionId,
        string $pageUrl,
        ?int $userId = null,
        ?string $pageTitle = null,
        ?string $referrer = null,
        ?string $userAgent = null,
        ?string $ipAddress = null
    ): int {
        $pdo = Database::getConnection();

        $deviceInfo = self::parseUserAgent($userAgent);

        $stmt = $pdo->prepare("
            INSERT INTO page_view
            (user_id, session_id, page_url, page_title, referrer, user_agent, ip_address, device_type, browser, os)
            VALUES (:user_id, :session_id, :page_url, :page_title, :referrer, :user_agent, :ip_address, :device_type, :browser, :os)
        ");

        $stmt->execute([
            ':user_id' => $userId,
            ':session_id' => $sessionId,
            ':page_url' => $pageUrl,
            ':page_title' => $pageTitle,
            ':referrer' => $referrer,
            ':user_agent' => $userAgent,
            ':ip_address' => $ipAddress,
            ':device_type' => $deviceInfo['device'],
            ':browser' => $deviceInfo['browser'],
            ':os' => $deviceInfo['os']
        ]);

        self::updateSession($sessionId, $userId);

        return (int)$pdo->lastInsertId();
    }

    private static function updateSession(string $sessionId, ?int $userId): void {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            INSERT INTO analytics_session (session_id, user_id, page_views)
            VALUES (:session_id, :user_id, 1)
            ON DUPLICATE KEY UPDATE
                last_activity = CURRENT_TIMESTAMP,
                page_views = page_views + 1,
                user_id = COALESCE(:user_id2, user_id)
        ");

        $stmt->execute([
            ':session_id' => $sessionId,
            ':user_id' => $userId,
            ':user_id2' => $userId
        ]);
    }

    private static function parseUserAgent(?string $userAgent): array {
        $result = [
            'device' => 'unknown',
            'browser' => null,
            'os' => null
        ];

        if (empty($userAgent)) {
            return $result;
        }

        // Detect device type
        if (preg_match('/Mobile|Android.*Mobile|iPhone|iPod/', $userAgent)) {
            $result['device'] = 'mobile';
        } elseif (preg_match('/iPad|Android(?!.*Mobile)|Tablet/', $userAgent)) {
            $result['device'] = 'tablet';
        } else {
            $result['device'] = 'desktop';
        }

        // Detect browser
        if (preg_match('/Firefox\/([0-9.]+)/', $userAgent, $m)) {
            $result['browser'] = 'Firefox';
        } elseif (preg_match('/Edg\/([0-9.]+)/', $userAgent, $m)) {
            $result['browser'] = 'Edge';
        } elseif (preg_match('/Chrome\/([0-9.]+)/', $userAgent, $m)) {
            $result['browser'] = 'Chrome';
        } elseif (preg_match('/Safari\/([0-9.]+)/', $userAgent, $m)) {
            $result['browser'] = 'Safari';
        } elseif (preg_match('/Opera|OPR/', $userAgent)) {
            $result['browser'] = 'Opera';
        }

        // Detect OS
        if (preg_match('/Windows NT/', $userAgent)) {
            $result['os'] = 'Windows';
        } elseif (preg_match('/Mac OS X/', $userAgent)) {
            $result['os'] = 'macOS';
        } elseif (preg_match('/Linux/', $userAgent)) {
            $result['os'] = 'Linux';
        } elseif (preg_match('/Android/', $userAgent)) {
            $result['os'] = 'Android';
        } elseif (preg_match('/iOS|iPhone|iPad/', $userAgent)) {
            $result['os'] = 'iOS';
        }

        return $result;
    }

    public static function getPageViewStats(string $startDate, string $endDate): array {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT
                DATE(created_at) as date,
                COUNT(*) as total_views,
                COUNT(DISTINCT session_id) as unique_sessions,
                COUNT(DISTINCT user_id) as logged_in_users
            FROM page_view
            WHERE created_at BETWEEN :start_date AND :end_date
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");

        $stmt->execute([
            ':start_date' => $startDate . ' 00:00:00',
            ':end_date' => $endDate . ' 23:59:59'
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getTopPages(int $limit = 10, ?string $startDate = null, ?string $endDate = null): array {
        $pdo = Database::getConnection();

        $sql = "
            SELECT
                page_url,
                page_title,
                COUNT(*) as views,
                COUNT(DISTINCT session_id) as unique_visitors
            FROM page_view
        ";

        $params = [];
        if ($startDate && $endDate) {
            $sql .= " WHERE created_at BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $startDate . ' 00:00:00';
            $params[':end_date'] = $endDate . ' 23:59:59';
        }

        $sql .= " GROUP BY page_url, page_title ORDER BY views DESC LIMIT " . (int)$limit;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getDeviceStats(?string $startDate = null, ?string $endDate = null): array {
        $pdo = Database::getConnection();

        $sql = "
            SELECT
                device_type,
                COUNT(*) as views,
                COUNT(DISTINCT session_id) as sessions
            FROM page_view
        ";

        $params = [];
        if ($startDate && $endDate) {
            $sql .= " WHERE created_at BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $startDate . ' 00:00:00';
            $params[':end_date'] = $endDate . ' 23:59:59';
        }

        $sql .= " GROUP BY device_type ORDER BY views DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getBrowserStats(?string $startDate = null, ?string $endDate = null): array {
        $pdo = Database::getConnection();

        $sql = "
            SELECT
                browser,
                COUNT(*) as views
            FROM page_view
            WHERE browser IS NOT NULL
        ";

        $params = [];
        if ($startDate && $endDate) {
            $sql .= " AND created_at BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $startDate . ' 00:00:00';
            $params[':end_date'] = $endDate . ' 23:59:59';
        }

        $sql .= " GROUP BY browser ORDER BY views DESC LIMIT 10";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getOsStats(?string $startDate = null, ?string $endDate = null): array {
        $pdo = Database::getConnection();

        $sql = "
            SELECT
                os,
                COUNT(*) as views
            FROM page_view
            WHERE os IS NOT NULL
        ";

        $params = [];
        if ($startDate && $endDate) {
            $sql .= " AND created_at BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $startDate . ' 00:00:00';
            $params[':end_date'] = $endDate . ' 23:59:59';
        }

        $sql .= " GROUP BY os ORDER BY views DESC LIMIT 10";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getReferrerStats(int $limit = 10, ?string $startDate = null, ?string $endDate = null): array {
        $pdo = Database::getConnection();

        $sql = "
            SELECT
                CASE
                    WHEN referrer IS NULL OR referrer = '' THEN 'Direct'
                    ELSE SUBSTRING_INDEX(SUBSTRING_INDEX(referrer, '://', -1), '/', 1)
                END as referrer_domain,
                COUNT(*) as visits
            FROM page_view
        ";

        $params = [];
        if ($startDate && $endDate) {
            $sql .= " WHERE created_at BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $startDate . ' 00:00:00';
            $params[':end_date'] = $endDate . ' 23:59:59';
        }

        $sql .= " GROUP BY referrer_domain ORDER BY visits DESC LIMIT " . (int)$limit;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getHourlyStats(?string $startDate = null, ?string $endDate = null): array {
        $pdo = Database::getConnection();

        $sql = "
            SELECT
                HOUR(created_at) as hour,
                COUNT(*) as views
            FROM page_view
        ";

        $params = [];
        if ($startDate && $endDate) {
            $sql .= " WHERE created_at BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $startDate . ' 00:00:00';
            $params[':end_date'] = $endDate . ' 23:59:59';
        }

        $sql .= " GROUP BY HOUR(created_at) ORDER BY hour ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getActiveUsers(int $minutes = 5): int {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT session_id) as active_users
            FROM page_view
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL :minutes MINUTE)
        ");

        $stmt->bindValue(':minutes', $minutes, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['active_users'] ?? 0);
    }

    public static function getSummary(?string $startDate = null, ?string $endDate = null): array {
        $pdo = Database::getConnection();

        $sql = "
            SELECT
                COUNT(*) as total_views,
                COUNT(DISTINCT session_id) as unique_sessions,
                COUNT(DISTINCT user_id) as unique_users,
                COUNT(DISTINCT DATE(created_at)) as days_tracked
            FROM page_view
        ";

        $params = [];
        if ($startDate && $endDate) {
            $sql .= " WHERE created_at BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $startDate . ' 00:00:00';
            $params[':end_date'] = $endDate . ' 23:59:59';
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
