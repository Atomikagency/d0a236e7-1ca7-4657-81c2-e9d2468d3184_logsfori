<?php

namespace LogsForI;

use WpOrg\Requests\Exception\InvalidArgument;

class Logger
{
//    const ENDPOINT = 'https://api.logsfori.com/push-log';
    const ENDPOINT = 'http://127.0.0.1:3000/push-log';
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

        if(session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = get_option('logsfori_token');
        if (empty($token)) {
            throw new InvalidArgument('Token is required');
        }

        $this->validateSeverity($severity);
        $min_severity = self::getMinimumSeverity();
        if (self::ALLOWED_SEVERITIES[$severity] < self::ALLOWED_SEVERITIES[$min_severity]) {
            return;
        }

        $timestamp = $timestamp ?? round(microtime(true) * 1000);

        if($transactionId === null) {
            $transactionId = session_id();
        }

        $extraData = [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'url' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'user_id' => wp_get_current_user()->ID ?? 'guest'
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

        $curl = curl_init(self::ENDPOINT);

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
}
