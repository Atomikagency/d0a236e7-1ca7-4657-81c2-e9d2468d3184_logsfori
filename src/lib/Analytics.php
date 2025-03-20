<?php

namespace LogsForI;

class Analytics {

    private static $timers = [];

    public static function init() {
        new self();
    }

    public function __construct() {
        if (get_option('logsfori_enable_timer', false)) {
            add_action('wp', [$this, 'startTimer']);
            add_action('wp_footer', [$this, 'handleAnalyticsTimer']);
        }
    }

    public function startTimer() {
        self::$timers['page_load'] = microtime(true);
    }

    public function handleAnalyticsTimer() {
        if (is_admin() || wp_doing_ajax() || defined('DOING_CRON') || defined('WP_CLI') || $this->isRestApiRequest()) {
            return;
        }
        if (http_response_code() >= 400) {
            return;
        }

        if (!isset(self::$timers['page_load'])) {
            return;
        }

        $executionTime = round((microtime(true) - self::$timers['page_load']) * 1000, 2); // En ms
        $pageName = $this->getFormattedPageName();
        Logger::saveWordpressTimerStop($pageName, $executionTime);
    }

    private function getFormattedPageName() {
        $path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $segments = array_filter(explode('/', $path)); // Supprime les valeurs vides
        $finalName = !empty($segments) ? implode('-', array_map('sanitize_title_with_dashes', $segments)) : 'home';
        if (!empty($_GET)) {
            $queryKeys = array_keys($_GET);
            $suffix = sanitize_title_with_dashes(implode('-', $queryKeys));
            $finalName .= "-{$suffix}";
        }

        return $finalName;
    }


    /**
     * Détecte si la requête est une requête API REST.
     */
    private function isRestApiRequest() {
        return defined('REST_REQUEST') && REST_REQUEST;
    }
}
