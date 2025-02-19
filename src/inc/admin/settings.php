<?php

if(!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', 'logsfori_add_admin_page');

function logsfori_add_admin_page() {
    add_options_page(
        'LogsForI Settings',
        'LogsForI',
        'manage_options',
        'logsfori_settings',
        'logsfori_render_settings_page'
    );
}

function logsfori_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>LogsForI Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('logsfori_option_group');
            do_settings_sections('logsfori_settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

add_action('admin_init', 'logsfori_settings_init');

function logsfori_settings_init() {
    register_setting('logsfori_option_group', 'logsfori_token', 'logsfori_sanitize_token');
    register_setting('logsfori_option_group', 'logsfori_hooks');
    register_setting('logsfori_settings_group', 'logsfori_severity', [
        'default' => LogsForI\Logger::SEVERITY_INFO,
        'sanitize_callback' => 'sanitize_text_field'
    ]);

    add_settings_section(
        'logsfori_section',
        'LogsForI Settings',
        '',
        'logsfori_settings'
    );

    add_settings_section(
        'logsfori_section_hooks',
        'LogsForI Events',
        '',
        'logsfori_settings'
    );



    add_settings_field(
        'logsfori_token',
        'API Token',
        'logsfori_token_field_render',
        'logsfori_settings',
        'logsfori_section'
    );

    add_settings_field(
        'logsfori_hooks',
        'Manage Events',
        'logsfori_hooks_field_render',
        'logsfori_settings',
        'logsfori_section_hooks'
    );

    add_settings_field(
        'logsfori_severity',
        'Log Severity Level',
        'logsfori_severity_callback',
        'logsfori_settings',
        'logsfori_section'
    );
}

function logsfori_severity_callback() {
    $current_severity = get_option('logsfori_severity', LogsForI\Logger::SEVERITY_INFO);
    $severities = [
        LogsForI\Logger::SEVERITY_DEBUG => 'Debug',
        LogsForI\Logger::SEVERITY_INFO => 'Info',
        LogsForI\Logger::SEVERITY_WARNING => 'Warning',
        LogsForI\Logger::SEVERITY_ERROR => 'Error',
        LogsForI\Logger::SEVERITY_CRITICAL => 'Critical'
    ];

    echo '<select name="logsfori_severity">';
    foreach ($severities as $key => $label) {
        $selected = selected($current_severity, $key, false);
        echo "<option value=\"$key\" $selected>$label</option>";
    }
    echo '</select>';
    echo '<p class="description">Select the minimum severity level for logs. Logs with lower severity will not be recorded.</p>';
}

function logsfori_token_field_render() {
    $token = get_option('logsfori_token');
    echo "<input type='password' name='logsfori_token' value='" . esc_attr($token) . "' />";
}

function logsfori_hooks_field_render() {
    $hooks = [
        'wp_login' => 'User Login - Triggered when a user successfully logs into the site.',
        'wp_login_failed' => 'Failed Login - Triggered when a user fails to log in due to incorrect credentials.',
        'authenticate' => 'Failed Login (Unknown User) - Triggered when a login attempt is made with a non-existing username.',
        'retrieve_password_request' => 'Password Reset Request - Triggered when a user requests a password reset.',
        'transition_post_status' => 'Post Published - Triggered when a post transitions to "published" status.',
        'post_updated' => 'Post Updated - Triggered when a post is updated.',
        'before_delete_post' => 'Post Deleted - Triggered before a post is deleted.',
        'add_attachment' => 'Attachment Added - Triggered when a media file is uploaded to the WordPress library.',
        'user_register' => 'User Registered - Triggered when a new user account is created.',
        'delete_user' => 'User Deleted - Triggered when a user account is deleted.',
        'profile_update' => 'User Profile Updated - Triggered when a user updates their profile information.',
        'upgrader_process_complete' => 'Plugin or Theme Modified - Triggered when a plugin or theme is installed, updated, or removed.',
        'set_user_role' => 'User Role Changed - Triggered when a user’s role is modified (e.g., upgraded to admin).',
        'application_passwords_create_password' => 'Application Password Created - Triggered when an application password is generated for a user.',
        'application_passwords_delete_password' => 'Application Password Deleted - Triggered when an application password is removed.',
        'update_option_default_role' => 'Default User Role Changed - Triggered when the default role for new users is modified.',
        'update_option_users_can_register' => 'User Registration Setting Changed - Triggered when the setting "Anyone can register" is toggled.',
        'update_option_admin_email' => 'Admin Email Changed - Triggered when the site administrator email is updated.',
        'core_upgrade' => 'WordPress Core Updated - Triggered when WordPress is updated to a new version.'
    ];


    $enabled_hooks = get_option('logsfori_hooks', []);
    foreach ($hooks as $hook => $label) {
        $checked = in_array($hook, (array)$enabled_hooks) ? 'checked' : '';
        echo "<label><input type='checkbox' name='logsfori_hooks[]' value='$hook' $checked> $label</label><br>";
    }
}

function logsfori_sanitize_token($token) {
    return sanitize_text_field($token);
}

?>
