<?php

namespace LogsForI;

class FatalError {

    public static function init() {
        new self();
    }

    public function __construct() {
        add_action('shutdown', [$this, 'handleFatalError']);
    }

    public function handleFatalError() {
        $error = error_get_last();

        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $message = $error['message'] ?? 'Unknown error';
            $file = $error['file'] ?? 'unknown';
            $line = $error['line'] ?? 'unknown';
            $severity = 'critical';
            $transactionId = session_id() ?: uniqid('trx_', true);
            $timestamp = round(microtime(true) * 1000);

            $extra = [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'url' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
                'user_id' => wp_get_current_user()->ID ?? 'guest'
            ];

            (new Logger())->push('fatal_error', "Fatal error in $file on line $line: $message", $severity, $timestamp, $extra, $transactionId);
        }
    }
}
