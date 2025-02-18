<?php

/**
 * Plugin Name: LogsForI
 * Description: A good way to push logs or event and track them easily
 * Version: 1.0.0
 * Author: AtomikAgency
 * Author URI: https://atomikagency.fr/
 */

define('LOGSFORI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LOGSFORI_PLUGIN_URL', plugin_dir_url(__FILE__));

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

require_once LOGSFORI_PLUGIN_DIR . 'update-checker.php';