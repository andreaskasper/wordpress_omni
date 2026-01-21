<?php

namespace plugins\goo1\omni;

/**
 * File Integrity Monitor
 * Monitors WordPress core files for unauthorized changes
 *
 * @package plugins\goo1\omni
 * @author Andreas Kasper
 */
class FileIntegrity {

    /**
     * Initialize file integrity monitoring
     */
    public static function init() {
        // Schedule daily integrity check
        if (!wp_next_scheduled('goo1_omni_integrity_check')) {
            wp_schedule_event(time(), 'daily', 'goo1_omni_integrity_check');
        }

        add_action('goo1_omni_integrity_check', [__CLASS__, 'run_integrity_check']);
        
        // Admin notices for integrity issues
        add_action('admin_notices', [__CLASS__, 'show_integrity_notices']);
    }

    /**
     * Run integrity check on WordPress core files
     *
     * @return array Results of the check
     */
    public static function run_integrity_check() {
        global $wp_version;
        
        $results = array(
            'checked_at' => current_time('mysql'),
            'wp_version' => $wp_version,
            'modified_files' => array(),
            'missing_files' => array(),
            'unknown_files' => array(),
            'status' => 'clean'
        );

        // Get checksums from WordPress.org
        $checksums = self::get_core_checksums($wp_version);
        
        if (!$checksums) {
            $results['status'] = 'error';
            $results['error'] = 'Could not retrieve checksums from WordPress.org';
            config::set('file_integrity_last_check', $results);
            return $results;
        }

        // Check each core file
        foreach ($checksums as $file => $checksum) {
            $filepath = ABSPATH . $file;
            
            // Skip wp-content directory
            if (strpos($file, 'wp-content') === 0) {
                continue;
            }

            if (!file_exists($filepath)) {
                $results['missing_files'][] = $file;
                $results['status'] = 'compromised';
            } else {
                $file_checksum = md5_file($filepath);
                if ($file_checksum !== $checksum) {
                    $results['modified_files'][] = array(
                        'file' => $file,
                        'expected' => $checksum,
                        'actual' => $file_checksum
                    );
                    $results['status'] = 'compromised';
                }
            }
        }

        // Check for unknown files in wp-admin and wp-includes
        $unknown_files = self::find_unknown_files($checksums);
        if (!empty($unknown_files)) {
            $results['unknown_files'] = $unknown_files;
            $results['status'] = 'warning';
        }

        // Store results
        config::set('file_integrity_last_check', $results);
        
        // Send notification if compromised
        if ($results['status'] === 'compromised') {
            self::send_integrity_alert($results);
        }

        return $results;
    }

    /**
     * Get WordPress core checksums
     *
     * @param string $version WordPress version
     * @return array|false Checksums array or false on failure
     */
    private static function get_core_checksums($version) {
        $locale = get_locale();
        $url = "https://api.wordpress.org/core/checksums/1.0/?version={$version}&locale={$locale}";
        
        $response = wp_remote_get($url, array('timeout' => 30));
        
        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['checksums']) || !is_array($data['checksums'])) {
            return false;
        }

        return $data['checksums'];
    }

    /**
     * Find unknown files in core directories
     *
     * @param array $checksums Known checksums
     * @return array Unknown files
     */
    private static function find_unknown_files($checksums) {
        $unknown = array();
        $core_dirs = array('wp-admin', 'wp-includes');
        
        foreach ($core_dirs as $dir) {
            $path = ABSPATH . $dir;
            if (!is_dir($path)) {
                continue;
            }

            $files = self::scan_directory_recursive($path, ABSPATH);
            
            foreach ($files as $file) {
                // Normalize path
                $relative_path = str_replace('\\', '/', $file);
                
                // Check if file is in checksums
                if (!isset($checksums[$relative_path])) {
                    // Skip common legitimate files
                    if (self::is_legitimate_unknown_file($relative_path)) {
                        continue;
                    }
                    
                    $unknown[] = $relative_path;
                }
            }
        }

        return $unknown;
    }

    /**
     * Recursively scan directory for files
     *
     * @param string $dir Directory to scan
     * @param string $base_path Base path to make relative
     * @return array File paths
     */
    private static function scan_directory_recursive($dir, $base_path) {
        $files = array();
        
        if (!is_dir($dir)) {
            return $files;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $filepath = str_replace($base_path, '', $file->getPathname());
                $files[] = $filepath;
            }
        }

        return $files;
    }

    /**
     * Check if an unknown file is likely legitimate
     *
     * @param string $file File path
     * @return bool
     */
    private static function is_legitimate_unknown_file($file) {
        $legitimate_patterns = array(
            '/\.htaccess$/',
            '/\.gitignore$/',
            '/\.git\//',
            '/\.svn\//',
            '/\.DS_Store$/',
            '/Thumbs\.db$/',
            '/desktop\.ini$/',
            '/\.user\.ini$/',
            '/php\.ini$/',
            '/\.maintenance$/'
        );

        foreach ($legitimate_patterns as $pattern) {
            if (preg_match($pattern, $file)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Send integrity alert notification
     *
     * @param array $results Check results
     */
    private static function send_integrity_alert($results) {
        // Check if notifications are enabled
        $notify_pushover = config::get('integrity_notify_pushover');
        $notify_email = config::get('integrity_notify_email', true); // Default to true

        $message = self::format_alert_message($results);

        // Send Pushover notification if configured
        if ($notify_pushover && class_exists('\plugins\goo1\omni\Pushover')) {
            \plugins\goo1\omni\Pushover::send(
                'File Integrity Alert',
                $message,
                1  // High priority
            );
        }

        // Send email notification if enabled
        if ($notify_email) {
            $admin_email = get_option('admin_email');
            $site_name = get_bloginfo('name');
            
            wp_mail(
                $admin_email,
                "[{$site_name}] File Integrity Alert",
                $message,
                array('Content-Type: text/html; charset=UTF-8')
            );
        }
    }

    /**
     * Format alert message
     *
     * @param array $results Check results
     * @return string Formatted message
     */
    private static function format_alert_message($results) {
        $site_url = get_site_url();
        $message = "File integrity issues detected on {$site_url}\n\n";

        if (!empty($results['modified_files'])) {
            $message .= "Modified Core Files (" . count($results['modified_files']) . "):\n";
            foreach ($results['modified_files'] as $file) {
                $message .= "- {$file['file']}\n";
            }
            $message .= "\n";
        }

        if (!empty($results['missing_files'])) {
            $message .= "Missing Core Files (" . count($results['missing_files']) . "):\n";
            foreach ($results['missing_files'] as $file) {
                $message .= "- {$file}\n";
            }
            $message .= "\n";
        }

        if (!empty($results['unknown_files'])) {
            $message .= "Unknown Files in Core Directories (" . count($results['unknown_files']) . "):\n";
            $count = 0;
            foreach ($results['unknown_files'] as $file) {
                if ($count++ < 10) {  // Limit to first 10
                    $message .= "- {$file}\n";
                }
            }
            if (count($results['unknown_files']) > 10) {
                $message .= "... and " . (count($results['unknown_files']) - 10) . " more\n";
            }
        }

        $message .= "\nPlease review these changes immediately.";

        return $message;
    }

    /**
     * Show admin notices for integrity issues
     */
    public static function show_integrity_notices() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Check if dashboard notices are enabled
        $notify_dashboard = config::get('integrity_notify_dashboard', true); // Default to true
        if (!$notify_dashboard) {
            return;
        }

        $last_check = config::get('file_integrity_last_check');
        
        if (!$last_check || !isset($last_check['status'])) {
            return;
        }

        if ($last_check['status'] === 'compromised') {
            $modified_count = count($last_check['modified_files'] ?? array());
            $missing_count = count($last_check['missing_files'] ?? array());
            
            ?>
            <div class="notice notice-error">
                <p>
                    <span class="dashicons dashicons-warning"></span>
                    <strong><?php _e('File Integrity Alert:', 'goo1-omni'); ?></strong>
                    <?php 
                    printf(
                        __('%d modified and %d missing WordPress core files detected!', 'goo1-omni'),
                        $modified_count,
                        $missing_count
                    ); 
                    ?>
                    <a href="<?php echo admin_url('options-general.php?page=goo1omni-settings&tab=security'); ?>">
                        <?php _e('View Details', 'goo1-omni'); ?>
                    </a>
                </p>
            </div>
            <?php
        } elseif ($last_check['status'] === 'warning') {
            $unknown_count = count($last_check['unknown_files'] ?? array());
            
            ?>
            <div class="notice notice-warning">
                <p>
                    <span class="dashicons dashicons-info"></span>
                    <strong><?php _e('File Integrity Warning:', 'goo1-omni'); ?></strong>
                    <?php 
                    printf(
                        __('%d unknown files found in core directories.', 'goo1-omni'),
                        $unknown_count
                    ); 
                    ?>
                    <a href="<?php echo admin_url('options-general.php?page=goo1omni-settings&tab=security'); ?>">
                        <?php _e('View Details', 'goo1-omni'); ?>
                    </a>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Get last check results
     *
     * @return array|null
     */
    public static function get_last_check() {
        return config::get('file_integrity_last_check');
    }

    /**
     * Force an immediate integrity check
     *
     * @return array Check results
     */
    public static function force_check() {
        return self::run_integrity_check();
    }

    /**
     * Clear integrity check results
     */
    public static function clear_results() {
        config::set('file_integrity_last_check', null);
    }
}
