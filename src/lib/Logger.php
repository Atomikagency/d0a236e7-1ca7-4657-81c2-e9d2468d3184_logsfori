<?php

namespace LogsForI;

use WpOrg\Requests\Exception\InvalidArgument;

class Logger
{
//    const ENDPOINT = 'https://api.logsfori.com/push-log';
    const ENDPOINT = 'http://127.0.0.1:3000';
    const SEVERITY_DEBUG = 'debug';
    const SEVERITY_INFO = 'info';
    const SEVERITY_WARNING = 'warning';
    const SEVERITY_ERROR = 'error';
    const SEVERITY_CRITICAL = 'critical';

    const ALLOWED_SEVERITIES = [
        self::SEVERITY_DEBUG => 0,
        self::SEVERITY_INFO => 1,
        self::SEVERITY_WARNING => 2,
        self::SEVERITY_ERROR => 3,
        self::SEVERITY_CRITICAL => 4
    ];


    public function push(
        string $eventName,
        string $message,
        string $severity = self::SEVERITY_INFO,
        int    $timestamp = null,
        array  $extra = [],
        string $transactionId = null
    )
    {

        $token = get_option('logsfori_token');
        if (empty($token)) {
            throw new InvalidArgument('Token is required');
        }

        $this->validateSeverity($severity);
        $min_severity = self::getMinimumSeverity();
        if (self::ALLOWED_SEVERITIES[$severity] < self::ALLOWED_SEVERITIES[$min_severity]) {
            return;
        }

        $timestamp = $timestamp ?? time();


        if($transactionId === null) {
            $transactionId = session_id();
        }

        $extraData = [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'ip_forwarded' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'none',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'url' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'referrer' => $_SERVER['HTTP_REFERER'] ?? 'direct_access',
            'query_params' => json_encode($_GET),

            // Utilisateur
            'user_id' => wp_get_current_user()->ID ?? 'guest',
            'user_email' => wp_get_current_user()->user_email ?? 'guest',
            'user_roles' => implode(', ', wp_get_current_user()->roles) ?? 'guest',
            'user_display_name' => wp_get_current_user()->display_name ?? 'guest',

            // Page
            'current_page' => get_the_title() ?? 'unknown',
            'current_post_id' => get_the_ID() ?? 'none',
            'is_admin' => is_admin() ? 'yes' : 'no',

            // Système
            'php_version' => PHP_VERSION,
            'wp_version' => get_bloginfo('version'),
            'theme_active' => wp_get_theme()->get('Name'),
            'theme_version' => wp_get_theme()->get('Version'),
            'plugin_list' => json_encode(get_option('active_plugins')),

            // Performance
            'execution_time' => timer_stop(),
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),

            // Sécurité
            'is_ajax' => wp_doing_ajax() ? 'yes' : 'no',
            'is_rest' => defined('REST_REQUEST') && REST_REQUEST ? 'yes' : 'no',
            'is_cron' => defined('DOING_CRON') && DOING_CRON ? 'yes' : 'no',
            'is_cli' => defined('WP_CLI') && WP_CLI ? 'yes' : 'no',
        ];


        $extra = array_merge($extraData, $extra);

        $payload = [
            'transaction_id' => $transactionId,
            'token' => $token,
            'event_name' => $eventName,
            'message' => $message,
            'severity' => $severity,
            'created_at' => $timestamp,
            'extra' => $extra
        ];
        if (!empty($extra)) {
            $payload['extra'] = $extra;
        }

        $curl = curl_init(self::ENDPOINT.'/push-log');

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
        ]);
        curl_exec($curl);
        curl_close($curl);
    }

    public static function getMinimumSeverity() {
        return get_option('logsfori_severity', self::SEVERITY_INFO);
    }

    private function validateSeverity(string $severity) {
        $severities = [
            self::SEVERITY_DEBUG,
            self::SEVERITY_INFO,
            self::SEVERITY_WARNING,
            self::SEVERITY_ERROR,
            self::SEVERITY_CRITICAL
        ];
        if (!in_array($severity, $severities)) {
            throw new InvalidArgument('Invalid severity');
        }
    }

    public static function startTimer(string $timerName)
    {

        $_SESSION['logsfori_timers'][$timerName] = microtime(true);
    }

    public static function saveTimer(string $timerName)
    {
        if (!isset($_SESSION['logsfori_timers'][$timerName])) {
            return null;
        }

        $executionTime = round((microtime(true) - $_SESSION['logsfori_timers'][$timerName]) * 1000, 2);
        unset($_SESSION['logsfori_timers'][$timerName]);

        $token = get_option('logsfori_token');
        if (empty($token)) {
            throw new InvalidArgument('Token is required');
        }

        $payload = [
            'func_name' => $timerName,
            'token' => $token,
            'execution_time' => $executionTime,
            'created_at' => round(microtime(true) * 1000),
        ];

        $curl = curl_init(self::ENDPOINT . '/timer');

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
        ]);
        curl_exec($curl);
        curl_close($curl);
    }

    public static function saveWordpressTimerStop($timerName,$executionTime){
        $token = get_option('logsfori_token');
        if (empty($token)) {
            error_log('LogsForI: Token missing, cannot send timer log.');
            return;
        }
        $payload = [
            'func_name' => $timerName,
            'token' => $token,
            'execution_time' => $executionTime,
            'created_at' => round(microtime(true) * 1000),
        ];

        $curl = curl_init(self::ENDPOINT . '/timer');

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
        ]);
        curl_exec($curl);
        curl_close($curl);
    }

}
