<?php
class UserInfo {
    public static function getInfo(): array {
        return [
            'ip' => self::getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'browser' => self::getBrowser(),
            'os' => self::getOS(),
            'time' => date('Y-m-d H:i:s'),
            'timezone' => date_default_timezone_get(),
            'language' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'Unknown'
        ];
    }

    private static function getClientIp(): string {
        $ipKeys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ipKeys as $key) {
            if (isset($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    private static function getBrowser(): string {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $browsers = [
            'Chrome' => '/Chrome\/(\d+)/',
            'Firefox' => '/Firefox\/(\d+)/',
            'Safari' => '/Safari\/(\d+)/',
            'Edge' => '/Edg\/(\d+)/',
            'Opera' => '/OPR\/(\d+)/',
        ];

        foreach ($browsers as $browser => $pattern) {
            if (preg_match($pattern, $userAgent, $matches)) {
                return $browser . ' ' . $matches[1];
            }
        }

        return 'Unknown';
    }

    private static function getOS(): string {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $osList = [
            'Windows 10' => '/Windows NT 10/',
            'Windows 11' => '/Windows NT 11/',
            'Mac OS' => '/Mac OS X/',
            'Linux' => '/Linux/',
            'Android' => '/Android/',
            'iOS' => '/(iPhone|iPad)/',
        ];

        foreach ($osList as $os => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return $os;
            }
        }

        return 'Unknown';
    }

    public static function saveLastVisit(): void {
        setcookie('last_visit', date('Y-m-d H:i:s'), time() + 86400, '/');
        setcookie('visit_count', (int)($_COOKIE['visit_count'] ?? 0) + 1, time() + 86400, '/');
    }

    public static function getLastVisit(): ?string {
        return $_COOKIE['last_visit'] ?? null;
    }

    public static function getVisitCount(): int {
        return (int)($_COOKIE['visit_count'] ?? 0);
    }
}
?>