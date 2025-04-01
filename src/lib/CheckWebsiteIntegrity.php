<?php

namespace LogsForI;

class CheckWebsiteIntegrity
{
    public static function init()
    {
        add_action('logsfori_check_integrity', [self::class, 'check']);

        if (!wp_next_scheduled('logsfori_check_integrity')) {
            wp_schedule_event(time(), 'every_4_hours', 'logsfori_check_integrity');
        }
    }

    public static function check()
    {
        self::checkCoreIntegrity();
        self::checkPhpInUploads();
        self::checkSensitiveFilesWritable();
        self::checkInactivePlugins();
        self::checkCriticalFileIntegrity();
        self::checkSecurityConstants();
        self::scanDangerousFunctions();
        self::checkPermissions();
        self::checkSuspiciousRootFiles();
        self::checkDebugLog();
        self::checkAvailableUpdates();
        self::checkDiskSpace();
    }

    public static function checkDebugLog()
    {
        $log = WP_CONTENT_DIR . '/debug.log';
        if (file_exists($log)) {
            $size = filesize($log);
            if ($size > 5 * 1024 * 1024) { // 5 Mo
                self::push(
                    'file_debug.log.large',
                    'debug.log is growing unusually large.',
                    Logger::SEVERITY_WARNING,
                    ['size_bytes' => $size]
                );
            }
        }
    }


    public static function checkSuspiciousRootFiles()
    {
        $extensions = ['zip', 'sql', 'log', 'bak', 'tar', 'old', 'rar'];
        $suspicious = [];

        foreach (glob(ABSPATH . '*') as $file) {
            if (is_file($file)) {
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                if (in_array($ext, $extensions)) {
                    $suspicious[] = basename($file);
                }
            }
        }

        if (!empty($suspicious)) {
            self::push(
                'suspicious.root.files',
                'Suspicious backup or archive files found in the root directory.',
                Logger::SEVERITY_WARNING,
                ['files' => $suspicious]
            );
        }
    }


    public static function checkPermissions()
    {
        $paths = [
            ABSPATH . 'wp-config.php',
            ABSPATH . '.htaccess',
            ABSPATH . 'wp-content',
            ABSPATH . 'wp-content/uploads',
        ];

        $issues = [];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                $perms = substr(sprintf('%o', fileperms($path)), -4);
                if ((is_file($path) && $perms > 0644) || (is_dir($path) && $perms > 0755)) {
                    $issues[$path] = $perms;
                }
            }
        }

        if (!empty($issues)) {
            self::push(
                'insecure.files.permissions',
                'Some files or folders have insecure permissions.',
                Logger::SEVERITY_CRITICAL,
                ['permissions' => $issues]
            );
        }
    }


    public static function checkPhpInUploads()
    {
        $suspicious = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(WP_CONTENT_DIR . '/uploads')
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $suspicious[] = str_replace(ABSPATH, '', $file->getPathname());
            }
        }

        if (!empty($suspicious)) {
            self::push(
                'upload.phpfile.detected',
                'PHP files found in the uploads directory. Potential backdoor.',
                Logger::SEVERITY_CRITICAL,
                ['files' => $suspicious]
            );
        }
    }

    public static function checkSensitiveFilesWritable()
    {
        $files = ['wp-config.php', '.htaccess', '.env'];
        $writable = [];

        foreach ($files as $file) {
            $path = ABSPATH . $file;
            if (file_exists($path) && is_writable($path)) {
                $writable[] = $file;
            }
        }

        if (!empty($writable)) {
            self::push(
                'writable.sensitive.files',
                'Some sensitive files are writable by the server.',
                Logger::SEVERITY_WARNING,
                ['files' => $writable]
            );
        }
    }

    public static function checkInactivePlugins()
    {
        $inactive = [];
        $all_plugins = get_plugins();
        $active = get_option('active_plugins', []);

        foreach ($all_plugins as $path => $data) {
            if (!in_array($path, $active)) {
                $inactive[] = $data['Name'] ?? $path;
            }
        }

        if (!empty($inactive)) {
            self::push(
                'inactive.plugins.detected',
                'Inactive plugins found. Consider removing them for more security.',
                Logger::SEVERITY_WARNING,
                ['plugins' => $inactive]
            );
        }
    }

    public static function checkCoreIntegrity()
    {
        include_once ABSPATH . 'wp-admin/includes/update.php';

        $version = get_bloginfo('version');
        $locale = get_locale();
        $checksums = get_core_checksums($version, $locale);

        if (!$checksums || !is_array($checksums)) {
            self::push(
                'integrity.core.check.failed',
                'Unable to retrieve WordPress checksums from the API.',
                'critical'
            );
            return;
        }

        $modified_files = [];
        foreach ($checksums as $file => $hash) {
            $local_path = ABSPATH . $file;
            if (!file_exists($local_path)) {
                $modified_files[] = "$file (manquant)";
            } elseif (md5_file($local_path) !== $hash) {
                $modified_files[] = "$file (modifié)";
            }
        }

        $extra_files = array_merge(
            self::get_extra_files(ABSPATH . 'wp-admin', $checksums),
            self::get_extra_files(ABSPATH . WPINC, $checksums)
        );

        if (!empty($modified_files) || !empty($extra_files)) {
            $message = 'Some WordPress core files have been modified or additional files were detected.';
            $extra = [
                'modified' => $modified_files,
                'extra'    => $extra_files
            ];
            self::push('integrity.core.issue.detected', $message, Logger::SEVERITY_CRITICAL, $extra);
        }
    }

    public static function checkCriticalFileIntegrity()
    {
        $files = ['wp-config.php', '.htaccess'];
        foreach ($files as $file) {
            $path = ABSPATH . $file;
            if (file_exists($path)) {
                $current = md5_file($path);
                $stored = get_option("logsfori_hash_$file");
                if ($stored && $stored !== $current) {
                    self::push(
                        'critical.file.modified',
                        "$file has been modified since last check.",
                        Logger::SEVERITY_WARNING,
                        ['file' => $file]
                    );
                }
                update_option("logsfori_hash_$file", $current);
            }
        }
    }

    public static function checkSecurityConstants()
    {
        $missing = [];

        if (!defined('DISALLOW_FILE_EDIT')) $missing[] = 'DISALLOW_FILE_EDIT';
        if (!defined('DISALLOW_FILE_MODS')) $missing[] = 'DISALLOW_FILE_MODS';
        if (!defined('FORCE_SSL_ADMIN')) $missing[] = 'FORCE_SSL_ADMIN';

        if (!empty($missing)) {
            self::push(
                'missing.security.constants',
                'Some recommended security constants are not defined.',
                Logger::SEVERITY_INFO,
                ['missing' => $missing]
            );
        }
    }

    public static function scanDangerousFunctions()
    {
        $suspicious_files = [];
        $functions = ['eval', 'base64_decode', 'shell_exec', 'system', 'exec', 'passthru', 'proc_open', 'popen'];
        $dirs_to_scan = [ABSPATH . 'wp-content'];

        foreach ($dirs_to_scan as $dir) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $content = file_get_contents($file->getPathname());
                    foreach ($functions as $fn) {
                        if (stripos($content, $fn . '(') !== false) {
                            $suspicious_files[$file->getPathname()][] = $fn;
                        }
                    }
                }
            }
        }

        if (!empty($suspicious_files)) {
            self::push(
                'dangerous_functions_detected',
                'Suspicious PHP functions found in wp-content.',
                'critical',
                ['files' => $suspicious_files]
            );
        }
    }

    public static function checkAvailableUpdates()
    {
        $core = get_site_transient('update_core');
        if (!empty($core->updates[0]) && $core->updates[0]->response === 'upgrade') {
            self::push(
                'core_update_available',
                'A new WordPress core version is available.',
                'warning',
                [
                    'current_version' => get_bloginfo('version'),
                    'new_version'     => $core->updates[0]->current,
                    'recommended_action' => 'Update WordPress core to the latest version.'
                ]
            );
        }

        $plugins = get_site_transient('update_plugins');
        if (!empty($plugins->response)) {
            $updates = [];
            foreach ($plugins->response as $plugin => $data) {
                $updates[$plugin] = [
                    'current' => $data->old_version ?? '?',
                    'new'     => $data->new_version ?? '?'
                ];
            }

            self::push(
                'plugin_updates_available',
                'One or more plugins have updates available.',
                'notice',
                [
                    'updates' => $updates,
                    'recommended_action' => 'Update plugins to avoid known vulnerabilities.'
                ]
            );
        }

        $themes = get_site_transient('update_themes');
        if (!empty($themes->response)) {
            $updates = [];
            foreach ($themes->response as $theme => $data) {
                $updates[$theme] = [
                    'current' => $data['version'] ?? '?',
                    'new'     => $data['new_version'] ?? '?'
                ];
            }

            self::push(
                'theme_updates_available',
                'One or more themes have updates available.',
                'notice',
                [
                    'updates' => $updates,
                    'recommended_action' => 'Update themes to ensure compatibility and security.'
                ]
            );
        }
    }

    public static function checkDiskSpace()
    {
        $free = disk_free_space(ABSPATH);
        if ($free < 500 * 1024 * 1024) {
            self::push(
                'low_disk_space',
                'The server is running low on disk space.',
                'warning',
                [
                    'free_space_mb' => round($free / 1024 / 1024),
                    'recommended_action' => 'Free up space or upgrade your hosting plan.'
                ]
            );
        }
    }


    protected static function get_extra_files($dir, $checksums)
    {
        $base = trailingslashit($dir);
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base));
        $extra = [];

        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isFile()) {
                $relative_path = str_replace(ABSPATH, '', $fileinfo->getPathname());
                if (!isset($checksums[$relative_path])) {
                    $extra[] = $relative_path;
                }
            }
        }

        return $extra;
    }

    public static function push($event, $message, $severity = 'info', $extra = [])
    {
        (new Logger())->push($event, $message, $severity, time(), $extra);
    }
}
