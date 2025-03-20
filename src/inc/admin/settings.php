<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', 'logsfori_add_admin_page');

function logsfori_add_admin_page()
{
    add_menu_page(
        'LogsForI',
        'LogsForI',
        'manage_options',
        'logsfori_settings',
        'logsfori_render_settings_page',
        'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTM5IiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGhlaWdodD0iMTA2IiBmaWxsPSJub25lIj48ZyBjbGFzcz0iZmlsbHMiPjxwYXRoIGQ9Ik0xMzEuNTMzLDE0LjczOEwyNS42NTYsMTQuNzM4QzIxLjQ0NCwxNC43MzgsMTguMTg5LDExLjMzNywxOC4xODksNy4zNjlDMTguMTg5LDMuNDAxLDIxLjYzNSwwLjAwMCwyNS42NTYsMC4wMDBMMTMxLjUzMywwLjAwMEMxMzUuNzQ1LDAuMDAwLDEzOS4wMDAsMy40MDEsMTM5LjAwMCw3LjM2OUMxMzkuMDAwLDExLjMzNywxMzUuNTU0LDE0LjczOCwxMzEuNTMzLDE0LjczOFpaIiBjbGFzcz0ic3QyIiBzdHlsZT0iZmlsbDogcmdiKDI1NSwgMjU1LCAyNTUpOyBmaWxsLW9wYWNpdHk6IDE7Ii8+PC9nPjxnIGNsYXNzPSJmaWxscyI+PHBhdGggZD0iTTU4LjU4Nyw0NC45NzBMMTguMTg5LDQ0Ljk3MEMxMy45NzcsNDQuOTcwLDEwLjcyMiw0MS41NjksMTAuNzIyLDM3LjYwMUMxMC43MjIsMzMuNjMzLDE0LjE2OCwzMC4yMzIsMTguMTg5LDMwLjIzMkw1OC41ODcsMzAuMjMyQzYyLjc5OSwzMC4yMzIsNjYuMDU0LDMzLjYzMyw2Ni4wNTQsMzcuNjAxQzY2LjA1NCw0MS41NjksNjIuNjA3LDQ0Ljk3MCw1OC41ODcsNDQuOTcwWloiIGNsYXNzPSJzdDIiIHN0eWxlPSJmaWxsOiByZ2IoMjU1LCAyNTUsIDI1NSk7IGZpbGwtb3BhY2l0eTogMTsiLz48L2c+PGcgY2xhc3M9ImZpbGxzIj48cGF0aCBkPSJNMjYuNjEzLDc1Ljc2OEwxMy4wMTksNzUuNzY4QzguODA3LDc1Ljc2OCw1LjU1Miw3Mi4zNjcsNS41NTIsNjguMzk5QzUuNTUyLDY0LjQzMSw4Ljk5OSw2MS4wMzAsMTMuMDE5LDYxLjAzMEwyNi42MTMsNjEuMDMwQzMwLjgyNSw2MS4wMzAsMzQuMDgwLDY0LjQzMSwzNC4wODAsNjguMzk5QzM0LjA4MCw3Mi4zNjcsMzAuNjM0LDc1Ljc2OCwyNi42MTMsNzUuNzY4WloiIGNsYXNzPSJzdDEiIHN0eWxlPSJmaWxsOiByZ2IoMSwgMTAxLCAxODApOyBmaWxsLW9wYWNpdHk6IDE7Ii8+PC9nPjxnIGNsYXNzPSJmaWxscyI+PHBhdGggZD0iTTExMy4zNDQsMTA2LjAwMEw3LjQ2NywxMDYuMDAwQzMuMjU1LDEwNi4wMDAsMC4wMDAsMTAyLjU5OSwwLjAwMCw5OC42MzFDMC4wMDAsOTQuNjYzLDMuNDQ2LDkxLjI2Miw3LjQ2Nyw5MS4yNjJMMTEzLjM0NCw5MS4yNjJDMTE3LjU1Niw5MS4yNjIsMTIwLjgxMSw5NC42NjMsMTIwLjgxMSw5OC42MzFDMTIwLjgxMSwxMDIuNTk5LDExNy4zNjUsMTA2LjAwMCwxMTMuMzQ0LDEwNi4wMDBaWiIgY2xhc3M9InN0MiIgc3R5bGU9ImZpbGw6IHJnYigyNTUsIDI1NSwgMjU1KTsgZmlsbC1vcGFjaXR5OiAxOyIvPjwvZz48ZyBjbGFzcz0iZmlsbHMiPjxwYXRoIGQ9Ik0xMjQuMDY2LDQ0Ljk3MEw4My4wOTQsNDQuOTcwQzc4Ljg4Miw0NC45NzAsNzUuNjI3LDQxLjU2OSw3NS42MjcsMzcuNjAxQzc1LjYyNywzMy42MzMsNzkuMDczLDMwLjIzMiw4My4wOTQsMzAuMjMyTDEyNC4wNjYsMzAuMjMyQzEyOC4yNzgsMzAuMjMyLDEzMS41MzMsMzMuNjMzLDEzMS41MzMsMzcuNjAxQzEzMS41MzMsNDEuNTY5LDEyOC4wODcsNDQuOTcwLDEyNC4wNjYsNDQuOTcwWloiIGNsYXNzPSJzdDEiIHN0eWxlPSJmaWxsOiByZ2IoMSwgMTAxLCAxODApOyBmaWxsLW9wYWNpdHk6IDE7Ii8+PC9nPjxnIGNsYXNzPSJmaWxscyI+PHBhdGggZD0iTTExOC43MDUsNzUuNzY4TDUwLjkyOCw3NS43NjhDNDYuNzE2LDc1Ljc2OCw0My40NjEsNzIuMzY3LDQzLjQ2MSw2OC4zOTlDNDMuNDYxLDY0LjQzMSw0Ni45MDgsNjEuMDMwLDUwLjkyOCw2MS4wMzBMMTE4LjcwNSw2MS4wMzBDMTIyLjkxNyw2MS4wMzAsMTI2LjE3Miw2NC40MzEsMTI2LjE3Miw2OC4zOTlDMTI2LjE3Miw3Mi4zNjcsMTIyLjcyNiw3NS43NjgsMTE4LjcwNSw3NS43NjhaWiIgY2xhc3M9InN0MiIgc3R5bGU9ImZpbGw6IHJnYigyNTUsIDI1NSwgMjU1KTsgZmlsbC1vcGFjaXR5OiAxOyIvPjwvZz48L3N2Zz4=',
        60
    );

    add_submenu_page(
        'logsfori_settings',  // Parent = "LogsForI"
        'LogsForI - HOOK',  // Titre de la page
        'Hooks event',  // Texte dans le sous-menu
        'manage_options',  // Permission requise
        'logsfori_security',  // Slug de la page
        'logsfori_render_security_page'  // Fonction d'affichage
    );
}

function logsfori_enqueue_admin_styles($hook)
{
    $hooks = ['logsfori_page_logsfori_security', 'toplevel_page_logsfori_settings'];
    if (!in_array($hook, $hooks)) {
        return;
    }
    wp_enqueue_script('logsfori-tailwind', 'https://unpkg.com/@tailwindcss/browser@4', [], null);
}

add_action('admin_enqueue_scripts', 'logsfori_enqueue_admin_styles');

function logsfori_get_severity_levels()
{
    return array_keys(\LogsForI\Logger::ALLOWED_SEVERITIES);
}

/**
 * GENERAL PAGE SETTINGS
 * save token
 */
function logsfori_render_settings_page()
{
    include LOGSFORI_PLUGIN_DIR . '/templates/admin/general.php';
}

/**
 * HOOK PAGE SETTINGS
 * manage event settings
 */
function logsfori_render_security_page()
{
    logsfori_save_security_hooks();
    $logsfori_security_hooks = json_decode(get_option('logsfori_security_hooks', '[]'), true);
    include LOGSFORI_PLUGIN_DIR . '/templates/admin/security.php';
}

function logsfori_save_security_hooks()
{
    if (isset($_POST['security_settings'])) {

        if (!isset($_POST['logsfori_nonce']) || !wp_verify_nonce($_POST['logsfori_nonce'], 'logsfori_save_security')) {
            return;
        }
        if (!current_user_can('manage_options')) {
            return;
        }
        if (isset($_POST['hooks']) && is_array($_POST['hooks'])) {
            $clean_hooks = [];

            foreach ($_POST['hooks'] as $hook) {
                if (!empty($hook['hook_name']) && !empty($hook['severity'])) {
                    $clean_hooks[] = [
                        'hook_name' => sanitize_text_field($hook['hook_name']),
                        'severity' => sanitize_text_field($hook['severity']),
                    ];
                }
            }
            update_option('logsfori_security_hooks', json_encode($clean_hooks));
        }
    }

    if (isset($_POST['logsfori_apply_default_settings'])) {

        $logsfori_security_hooks = json_decode(get_option('logsfori_security_hooks', '[]'), true);
        $defaultHooks = [
            ['hook_name' => 'wp_login', 'severity' => 'info'],
            ['hook_name' => 'wp_login_failed', 'severity' => 'warning'],
            ['hook_name' => 'authenticate', 'severity' => 'warning'],
            ['hook_name' => 'retrieve_password_request', 'severity' => 'info'],
            ['hook_name' => 'transition_post_status', 'severity' => 'info'],
            ['hook_name' => 'post_updated', 'severity' => 'info'],
            ['hook_name' => 'before_delete_post', 'severity' => 'warning'],
            ['hook_name' => 'add_attachment', 'severity' => 'info'],
            ['hook_name' => 'user_register', 'severity' => 'info'],
            ['hook_name' => 'delete_user', 'severity' => 'error'],
            ['hook_name' => 'profile_update', 'severity' => 'info'],
            ['hook_name' => 'upgrader_process_complete', 'severity' => 'warning'],
            ['hook_name' => 'set_user_role', 'severity' => 'warning'],
            ['hook_name' => 'application_passwords_create_password', 'severity' => 'warning'],
            ['hook_name' => 'application_passwords_delete_password', 'severity' => 'warning'],
            ['hook_name' => 'update_option_default_role', 'severity' => 'warning'],
            ['hook_name' => 'update_option_users_can_register', 'severity' => 'info'],
            ['hook_name' => 'update_option_admin_email', 'severity' => 'warning'],
            ['hook_name' => 'core_upgrade', 'severity' => 'info'],
        ];

        if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            $defaultWoocommerceHooks = [
                ['hook_name' => 'woocommerce_new_order', 'severity' => 'info'],
                ['hook_name' => 'woocommerce_payment_complete', 'severity' => 'info'],
                ['hook_name' => 'woocommerce_payment_failed', 'severity' => 'warning'],
                ['hook_name' => 'woocommerce_order_status_cancelled', 'severity' => 'warning'],
                ['hook_name' => 'woocommerce_order_status_completed', 'severity' => 'info'],
                ['hook_name' => 'woocommerce_order_status_changed', 'severity' => 'info'],
                ['hook_name' => 'woocommerce_order_refunded', 'severity' => 'warning'],
                ['hook_name' => 'woocommerce_delete_product', 'severity' => 'error'],
                ['hook_name' => 'woocommerce_product_set_stock', 'severity' => 'info'],
                ['hook_name' => 'woocommerce_no_stock', 'severity' => 'critical'],
                ['hook_name' => 'woocommerce_created_customer', 'severity' => 'info'],
                ['hook_name' => 'woocommerce_delete_customer', 'severity' => 'warning'],
                ['hook_name' => 'woocommerce_cart_abandoned', 'severity' => 'warning'],
                ['hook_name' => 'woocommerce_applied_coupon', 'severity' => 'info'],
                ['hook_name' => 'woocommerce_payment_method_declined', 'severity' => 'warning'],
                ['hook_name' => 'woocommerce_update_option_woocommerce_version', 'severity' => 'info'],
                ['hook_name' => 'woocommerce_plugin_status_changed', 'severity' => 'warning'],
                ['hook_name' => 'woocommerce_critical_error', 'severity' => 'critical'],
            ];
            $defaultHooks = array_merge($defaultHooks,$defaultWoocommerceHooks);
        }

        foreach ($defaultHooks as $defaultHook) {
            $exists = false;
            foreach ($logsfori_security_hooks as $existingHook) {
                if ($existingHook['hook_name'] === $defaultHook['hook_name'] && $existingHook['severity'] === $defaultHook['severity']) {
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                $logsfori_security_hooks[] = $defaultHook;
            }
        }
        update_option('logsfori_security_hooks', json_encode($logsfori_security_hooks));
    }
}


add_action('admin_init', 'logsfori_settings_init');

function logsfori_settings_init()
{
    register_setting('logsfori_option_group', 'logsfori_token', 'logsfori_sanitize_token');
    register_setting('logsfori_option_group', 'logsfori_severity_min', [
        'default' => LogsForI\Logger::SEVERITY_INFO,
        'sanitize_callback' => 'sanitize_text_field'
    ]);
    register_setting('logsfori_option_group', 'logsfori_enable_timer');
}

function logsfori_sanitize_token($token)
{
    return sanitize_text_field($token);
}

function logsfori_save_token() {
    if (isset($_POST['logsfori_token'])) {
        $token = sanitize_text_field($_POST['logsfori_token']);
        update_option('logsfori_token', $token);
        $response = wp_remote_post(\LogsForI\Logger::ENDPOINT.'/validate-wordpress-connection', [
            'body'    => json_encode([
                'token'          => $token,
                'connection_url' => get_site_url()
            ]),
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'timeout' => 10,
        ]);

        if (is_wp_error($response)) {
            update_option('logsfori_connection_status', 'failed');
            error_log('LogsForI API Error: ' . $response->get_error_message());
            return;
        }


        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if(!empty($data['error'])){
            update_option('logsfori_connection_status', 'failed');
        }

        if (!isset($data['connection_status']) || $data['connection_status'] !== 'success') {
            update_option('logsfori_connection_status', 'failed');
            error_log('LogsForI API validation failed: ' . print_r($data, true));
            return;
        }

        update_option('logsfori_connection_status', 'success');
        update_option('logsfori_project_id', sanitize_text_field($data['data']['project_id']));
        update_option('logsfori_connection_id', sanitize_text_field($data['data']['connection_id']));
    }
}
add_action('admin_init', 'logsfori_save_token');

?>
