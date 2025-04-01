<?php

namespace LogsForI;

class AuditLog
{

    private static $options;


    public static function init()
    {

        $hooks = get_option('logsfori_security_hooks', '[]');
        $hooks = json_decode($hooks,true);
        if (empty($hooks)) $hooks = [];
        self::$options = $hooks;

        if (self::isEnabled('wp_login')) add_action('wp_login', [self::class, 'logLogin'], 10, 2);
        if (self::isEnabled('wp_login_failed')) add_action('wp_login_failed', [self::class, 'logFailedLogin']);
        if (self::isEnabled('authenticate')) add_filter('authenticate', [self::class, 'logFailedLoginUnknownUser'], 30, 2);
        if (self::isEnabled('retrieve_password_request')) add_action('retrieve_password_request', [self::class, 'logPasswordReset']);
        if (self::isEnabled('transition_post_status')) add_action('transition_post_status', [self::class, 'logPostPublished'], 10, 3);
        if (self::isEnabled('post_updated')) add_action('post_updated', [self::class, 'logPostUpdated'], 10, 3);
        if (self::isEnabled('add_attachment')) add_action('add_attachment', [self::class, 'logAddAttachment']);
        if (self::isEnabled('before_delete_post')) add_action('before_delete_post', [self::class, 'logBeforeDeletePost']);
        if (self::isEnabled('user_register')) add_action('user_register', [self::class, 'logUserRegistered']);
        if (self::isEnabled('delete_user')) add_action('delete_user', [self::class, 'logUserDeleted']);
        if (self::isEnabled('profile_update')) add_action('profile_update', [self::class, 'logUserUpdated'], 10, 2);
        if (self::isEnabled('upgrader_process_complete')) add_action('upgrader_process_complete', [self::class, 'logPluginOrThemeChange'], 10, 2);
        if (self::isEnabled('set_user_role')) add_action('set_user_role', [self::class, 'logUserRoleChange'], 10, 3);
        if (self::isEnabled('application_passwords_create_password')) add_action('application_passwords_create_password', [self::class, 'logAppPasswordCreated'], 10, 2);
        if (self::isEnabled('application_passwords_delete_password')) add_action('application_passwords_delete_password', [self::class, 'logAppPasswordDeleted'], 10, 2);
        if (self::isEnabled('update_option_default_role')) add_action('update_option_default_role', [self::class, 'logDefaultRoleChanged'], 10, 2);
        if (self::isEnabled('update_option_users_can_register')) add_action('update_option_users_can_register', [self::class, 'logUserRegistrationSettingChanged'], 10, 2);
        if (self::isEnabled('update_option_admin_email')) add_action('update_option_admin_email', [self::class, 'logAdminEmailChanged'], 10, 2);
        if (self::isEnabled('core_upgrade')) add_action('core_upgrade', [self::class, 'logWordPressUpdated']);

    }
    private static function isEnabled($hook)
    {
        return in_array($hook, array_column(self::$options, 'hook_name'));
    }

    public static function push($event, $message, $severity = 'info', $extra = []) {
        (new Logger())->push($event, $message, $severity, time(), $extra);
    }

    public static function logLogin($user_login, $user)
    {
        self::push('login_success', "User $user_login logged in.", Logger::SEVERITY_INFO, [
            'user_id' => $user->ID,
            'user_login' => $user_login,
            'user_email' => $user->user_email
        ]);
    }

    public static function logFailedLogin($username)
    {
        self::push('login_failed', "Login was failed for $username", Logger::SEVERITY_WARNING,[
            'username' => $username,
        ]);
    }

    public static function logFailedLoginUnknownUser($user,$username)
    {
        if ($user === null) {
            self::push('login_failed', "Attempt login with unknown user", Logger::SEVERITY_ERROR,[
                'username' => $username,
            ]);
        }
        return $user;
    }

    public static function logPasswordReset($user_login){
        self::push('password_reset', "Password reset request for $user_login", Logger::SEVERITY_INFO,[
            'user_login' => $user_login,
        ]);
    }

    public static function logBeforeDeletePost($post_id, $post){
        self::push('post.'.$post->post_type.'.deleted', "Post was deleted with ID".$post_id, Logger::SEVERITY_WARNING,[
            'post_id' => $post_id,
            'post_title' => $post->post_title
        ]);
    }

    public static function logPostPublished($new_status, $old_status, $post)
    {
        if ($new_status === 'publish' && $old_status !== 'publish') {
            self::push('post_published', "Post {$post->post_title} was published", Logger::SEVERITY_INFO,[
                'post_id' => $post->ID,
                'post_title' => $post->post_title,
                'post_type' => $post->post_type,
            ]);
        }
    }

    public static function logPostUpdated($post_ID, $post_after, $post_before){
        self::push('post_updated', "Post {$post_after->post_title} was updated", Logger::SEVERITY_INFO,[
            'post_id' => $post_after->ID,
            'post_title' => $post_after->post_title,
            'post_type' => $post_after->post_type,
        ]);
    }

    public static function logAddAttachment($post_ID){
        $post = get_post($post_ID);
        self::push('attachment_added', "Attachment {$post->post_title} was added", Logger::SEVERITY_INFO,[
            'post_id' => $post->ID,
            'post_title' => $post->post_title,
            'post_type' => $post->post_type,
        ]);
    }

    public static function logUserRegistered($user_id) {
        $user = get_user_by('ID', $user_id);
        self::push('user_registered', "User {$user->user_login} registered.", Logger::SEVERITY_INFO, [
            'user_id' => $user_id,
            'user_email' => $user->user_email
        ]);
    }

    public static function logUserDeleted($user_id, $reassign) {
        $user = get_user_by('ID', $user_id);
        $severity = in_array('administrator', $user->roles) ? Logger::SEVERITY_CRITICAL : Logger::SEVERITY_WARNING;

        self::push('user_deleted', "User {$user->user_login} (Email: {$user->user_email}) was deleted.", $severity, [
            'user_id' => $user_id,
            'user_email' => $user->user_email,
            'was_admin' => in_array('administrator', $user->roles)
        ]);
    }

    public static function logUserUpdated($user_id, $old_user_data) {
        $user = get_user_by('ID', $user_id);
        self::push('user_updated', "User {$user->user_login} was updated.", Logger::SEVERITY_INFO, [
            'user_id' => $user_id,
            'user_email' => $user->user_email
        ]);
    }

    public static function logPluginOrThemeChange($upgrader, $options) {
        $action = $options['action'];
        $type = $options['type'];
        $items = implode(', ', $options['plugins'] ?? []);

        $event = "{$type}_{$action}";
        $message = ucfirst($type) . " $action: $items";

        self::push($event, $message, Logger::SEVERITY_INFO, [
            'type' => $type,
            'action' => $action,
            'items' => $items
        ]);
    }

    public static function logUserRoleChange($user_id, $new_role, $old_roles) {
        $user = get_user_by('ID', $user_id);
        if (in_array('administrator', $new_role) && !in_array('administrator', $old_roles)) {
            self::push('user_granted_admin', "User {$user->user_login} was granted admin role.", Logger::SEVERITY_WARNING, [
                'user_id' => $user_id,
                'user_email' => $user->user_email
            ]);
        }
    }

    public static function logAppPasswordCreated($user_id, $password_name) {
        self::push('app_password_created', "User ID $user_id created an application password: $password_name", Logger::SEVERITY_INFO, [
            'user_id' => $user_id,
            'password_name' => $password_name
        ]);
    }

    public static function logAppPasswordDeleted($user_id, $password_name) {
        self::push('app_password_deleted', "User ID $user_id deleted an application password: $password_name", Logger::SEVERITY_WARNING, [
            'user_id' => $user_id,
            'password_name' => $password_name
        ]);
    }

    public static function logDefaultRoleChanged($old_value, $new_value) {
        self::push('default_role_changed', "Default user role changed from $old_value to $new_value.", Logger::SEVERITY_INFO);
    }

    public static function logUserRegistrationSettingChanged($old_value, $new_value) {
        $status = $new_value ? 'enabled' : 'disabled';
        self::push('user_registration_setting_changed', "User registration setting changed to $status.", Logger::SEVERITY_INFO);
    }

    public static function logAdminEmailChanged($old_value, $new_value) {
        self::push('admin_email_changed', "Admin email changed from $old_value to $new_value.", Logger::SEVERITY_WARNING);
    }

    public static function logWordPressUpdated() {
        self::push('wordpress_updated', "WordPress core has been updated.", Logger::SEVERITY_INFO);
    }
}