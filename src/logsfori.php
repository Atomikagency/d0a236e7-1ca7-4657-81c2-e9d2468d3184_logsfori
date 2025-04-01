<?php

/**
 * Plugin Name: LogsForI
 * Description: A good way to push logs or event and track them easily
 * Version: 1.0.6
 * Author: Kevin JANIKY
 * Author URI: https://atomikagency.fr/
 */

define('LOGSFORI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LOGSFORI_PLUGIN_URL', plugin_dir_url(__FILE__));

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

function logsfori_start_session()
{
    if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
        session_start();
    }
}
add_action('init', 'logsfori_start_session', 1);



require_once LOGSFORI_PLUGIN_DIR . 'lib/Logger.php';
require_once LOGSFORI_PLUGIN_DIR . 'lib/AuditLog.php';
require_once LOGSFORI_PLUGIN_DIR . 'lib/FatalError.php';
require_once LOGSFORI_PLUGIN_DIR . 'lib/Analytics.php';
require_once LOGSFORI_PLUGIN_DIR . 'lib/WoocommerceEvent.php';
require_once LOGSFORI_PLUGIN_DIR . 'lib/CheckWebsiteIntegrity.php';

require_once LOGSFORI_PLUGIN_DIR . 'update-checker.php';
require_once LOGSFORI_PLUGIN_DIR . 'inc/admin/settings.php';

add_action('init', [\LogsFori\AuditLog::class, 'init']);
add_action('init', [\LogsForI\FatalError::class,'init']);
add_action('plugins_loaded', [\LogsForI\WoocommerceEvent::class, 'init']);
add_action('init', [\LogsForI\Analytics::class,'init']);
add_filter('cron_schedules', function ($schedules) {
    $schedules['every_4_hours'] = [
        'interval' => 4 * 60 * 60, // 4 heures en secondes
        'display'  => __('Toutes les 4 heures'),
    ];
    return $schedules;
});
add_action('init', [\LogsForI\CheckWebsiteIntegrity::class, 'init']);


