<?php

require_once __DIR__ . '/../app/Models/Analytics.php';
require_once __DIR__ . '/Auth.php';

class AnalyticsMiddleware {
    
    public static function track(?string $pageTitle = null): void {
        // Get or create analytics session ID
        if (empty($_COOKIE['analytics_session'])) {
            $sessionId = bin2hex(random_bytes(32));
            setcookie('analytics_session', $sessionId, [
                'expires' => time() + 86400 * 30, // 30 days
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        } else {
            $sessionId = $_COOKIE['analytics_session'];
        }
        
        $user = Auth::user();
        
        Analytics::trackPageView(
            sessionId: $sessionId,
            pageUrl: $_SERVER['REQUEST_URI'] ?? '/',
            userId: $user->id ?? null,
            pageTitle: $pageTitle,
            referrer: $_SERVER['HTTP_REFERER'] ?? null,
            userAgent: $_SERVER['HTTP_USER_AGENT'] ?? null,
            ipAddress: self::getClientIp()
        );
    }

    private static function getClientIp(): ?string {
        $headers = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_X_FORWARDED_FOR',      // Proxy
            'HTTP_X_REAL_IP',            // Nginx
            'REMOTE_ADDR'
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return null;
    }
}
