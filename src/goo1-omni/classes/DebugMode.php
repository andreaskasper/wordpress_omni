<?php

namespace plugins\goo1\omni;

/**
 * Debug Mode Manager
 * Provides quick toggle for WP_DEBUG and related debugging constants
 *
 * @package plugins\goo1\omni
 * @author Andreas Kasper
 */
class DebugMode {

    /**
     * Initialize debug mode functionality
     */
    public static function init() {
        // Admin notice to show current debug status
        add_action('admin_notices', [__CLASS__, 'show_debug_notice']);
        
        // Handle debug toggle action
        add_action('admin_init', [__CLASS__, 'handle_debug_toggle']);
    }

    /**
     * Check if debug mode is enabled
     *
     * @return bool True if WP_DEBUG is defined and true
     */
    public static function is_debug_enabled() {
        return defined('WP_DEBUG') && WP_DEBUG === true;
    }

    /**
     * Check if debug log is enabled
     *
     * @return bool True if WP_DEBUG_LOG is enabled
     */
    public static function is_debug_log_enabled() {
        return defined('WP_DEBUG_LOG') && WP_DEBUG_LOG === true;
    }

    /**
     * Check if debug display is enabled
     *
     * @return bool True if WP_DEBUG_DISPLAY is enabled
     */
    public static function is_debug_display_enabled() {
        return defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY === true;
    }

    /**
     * Show admin notice with current debug status
     */
    public static function show_debug_notice() {
        // Only show to administrators
        if (!current_user_can('manage_options')) {
            return;
        }

        // Only show on goo1 omni settings page
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'goo1omni-settings') === false) {
            return;
        }

        $is_debug = self::is_debug_enabled();
        $notice_class = $is_debug ? 'notice-warning' : 'notice-info';
        $icon = $is_debug ? 'warning' : 'info';
        
        ?>
        <div class="notice <?php echo esc_attr($notice_class); ?> is-dismissible">
            <p>
                <span class="dashicons dashicons-<?php echo esc_attr($icon); ?>"></span>
                <strong><?php _e('Debug Mode Status:', 'goo1-omni'); ?></strong>
                <?php if ($is_debug): ?>
                    <?php _e('Debug mode is currently ENABLED', 'goo1-omni'); ?>
                <?php else: ?>
                    <?php _e('Debug mode is currently DISABLED', 'goo1-omni'); ?>
                <?php endif; ?>
            </p>
        </div>
        <?php
    }

    /**
     * Handle debug mode toggle action
     */
    public static function handle_debug_toggle() {
        // Check if this is a debug toggle request
        if (!isset($_GET['goo1_debug_action'])) {
            return;
        }

        // Verify user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'goo1-omni'));
        }

        // Verify nonce
        if (!isset($_GET['goo1_debug_nonce']) || !wp_verify_nonce($_GET['goo1_debug_nonce'], 'goo1_debug_toggle')) {
            wp_die(__('Security check failed.', 'goo1-omni'));
        }

        $action = sanitize_text_field($_GET['goo1_debug_action']);
        
        if ($action === 'toggle') {
            $result = self::toggle_debug_mode();
            
            // Redirect with success message
            $redirect_url = add_query_arg(
                array(
                    'page' => 'goo1omni-settings',
                    'tab' => 'developer',
                    'debug_toggled' => $result ? 'enabled' : 'disabled'
                ),
                admin_url('options-general.php')
            );
            
            wp_safe_redirect($redirect_url);
            exit;
        }
    }

    /**
     * Toggle debug mode in wp-config.php
     *
     * @return bool|null True if enabled, False if disabled, Null on error
     */
    public static function toggle_debug_mode() {
        $wp_config_path = self::get_wp_config_path();
        
        if (!$wp_config_path || !is_writable($wp_config_path)) {
            return null;
        }

        $config_content = file_get_contents($wp_config_path);
        $is_currently_enabled = self::is_debug_enabled();
        
        // Backup the config file
        $backup_path = $wp_config_path . '.bak.' . date('YmdHis');
        file_put_contents($backup_path, $config_content);

        if ($is_currently_enabled) {
            // Disable debug mode
            $config_content = self::set_debug_constants($config_content, false);
            $new_state = false;
        } else {
            // Enable debug mode
            $config_content = self::set_debug_constants($config_content, true);
            $new_state = true;
        }

        // Write the updated config
        $result = file_put_contents($wp_config_path, $config_content);
        
        if ($result !== false) {
            // Store last toggle time
            config::set('debug_last_toggled', current_time('mysql'));
            config::set('debug_toggled_by', get_current_user_id());
            
            return $new_state;
        }

        // Restore backup on failure
        file_put_contents($wp_config_path, file_get_contents($backup_path));
        return null;
    }

    /**
     * Set debug constants in wp-config.php content
     *
     * @param string $content The wp-config.php content
     * @param bool $enable True to enable, false to disable
     * @return string Modified content
     */
    private static function set_debug_constants($content, $enable) {
        $debug_value = $enable ? 'true' : 'false';
        
        // Define the debug settings to update
        $constants = array(
            'WP_DEBUG',
            'WP_DEBUG_LOG',
            'WP_DEBUG_DISPLAY',
            'SCRIPT_DEBUG'
        );

        foreach ($constants as $constant) {
            $pattern = "/define\s*\(\s*['\"]" . $constant . "['\"]\s*,\s*(true|false)\s*\)\s*;/i";
            $replacement = "define( '" . $constant . "', " . $debug_value . " );";
            
            if (preg_match($pattern, $content)) {
                // Update existing constant
                $content = preg_replace($pattern, $replacement, $content);
            } else {
                // Add new constant before "That's all, stop editing!"
                $insert_point = "/* That's all, stop editing!";
                if (strpos($content, $insert_point) !== false) {
                    $replacement_with_comment = "\n// Debug mode (managed by goo1 Omni)\n" . $replacement . "\n\n" . $insert_point;
                    $content = str_replace($insert_point, $replacement_with_comment, $content);
                }
            }
        }

        return $content;
    }

    /**
     * Get the path to wp-config.php
     *
     * @return string|false Path to wp-config.php or false if not found
     */
    private static function get_wp_config_path() {
        // Try standard location
        if (file_exists(ABSPATH . 'wp-config.php')) {
            return ABSPATH . 'wp-config.php';
        }

        // Try one level up
        if (file_exists(dirname(ABSPATH) . '/wp-config.php')) {
            return dirname(ABSPATH) . '/wp-config.php';
        }

        return false;
    }

    /**
     * Get debug log file path
     *
     * @return string Path to debug.log
     */
    public static function get_debug_log_path() {
        return WP_CONTENT_DIR . '/debug.log';
    }

    /**
     * Check if debug log file exists
     *
     * @return bool
     */
    public static function debug_log_exists() {
        return file_exists(self::get_debug_log_path());
    }

    /**
     * Get debug log file size
     *
     * @return int File size in bytes
     */
    public static function get_debug_log_size() {
        $log_path = self::get_debug_log_path();
        return file_exists($log_path) ? filesize($log_path) : 0;
    }

    /**
     * Get last N lines from debug log
     *
     * @param int $lines Number of lines to retrieve
     * @return string Log content
     */
    public static function get_debug_log_tail($lines = 100) {
        $log_path = self::get_debug_log_path();
        
        if (!file_exists($log_path)) {
            return '';
        }

        $file = new \SplFileObject($log_path, 'r');
        $file->seek(PHP_INT_MAX);
        $total_lines = $file->key();
        
        $start_line = max(0, $total_lines - $lines);
        $file->seek($start_line);
        
        $output = '';
        while (!$file->eof()) {
            $output .= $file->current();
            $file->next();
        }
        
        return $output;
    }

    /**
     * Clear debug log file
     *
     * @return bool Success status
     */
    public static function clear_debug_log() {
        $log_path = self::get_debug_log_path();
        
        if (!file_exists($log_path)) {
            return true;
        }

        return file_put_contents($log_path, '') !== false;
    }

    /**
     * Check if wp-config.php is writable
     *
     * @return bool
     */
    public static function is_wp_config_writable() {
        $wp_config_path = self::get_wp_config_path();
        return $wp_config_path && is_writable($wp_config_path);
    }
}
