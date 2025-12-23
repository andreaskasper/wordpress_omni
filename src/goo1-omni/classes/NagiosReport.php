<?php

namespace plugins\goo1\omni;

class NagiosReport {

    public static function test() {
        $out = array();
        
        // Test each section independently with error isolation
        $out['wordpress'] = self::safe_call('get_wordpress_info');
        $out['plugins'] = self::safe_call('get_plugins_info');
        $out['themes'] = self::safe_call('get_themes_info');
        $out['users'] = self::safe_call('get_users_info');
        $out['database'] = self::safe_call('get_database_info');
        $out['security'] = self::safe_call('get_security_info');
        $out['performance'] = self::safe_call('get_performance_info');
        $out['media'] = self::safe_call('get_media_info');
        $out['updates'] = self::safe_call('get_updates_info');
        $out['server'] = self::safe_call('get_server_info');

        header("Content-Type: application/json");
        echo json_encode($out, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Safely call a method and return result or error
     */
    private static function safe_call($method_name) {
        try {
            return call_user_func(array(__CLASS__, $method_name));
        } catch (\Exception $e) {
            return array(
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            );
        } catch (\Error $e) {
            return array(
                'fatal_error' => $e->getMessage(),
                'line' => $e->getLine(),
            );
        }
    }

    /**
     * 📊 Get WordPress Core Information
     */
    private static function get_wordpress_info() {
        global $wp_version;

        return array(
            'version' => $wp_version,
            'site_url' => get_site_url(),
            'home_url' => get_home_url(),
            'is_multisite' => is_multisite(),
            'language' => get_locale(),
            'timezone' => wp_timezone_string(),
            'debug_mode' => defined('WP_DEBUG') && WP_DEBUG,
            'debug_log' => defined('WP_DEBUG_LOG') && WP_DEBUG_LOG,
            'memory_limit' => WP_MEMORY_LIMIT,
            'max_memory_limit' => WP_MAX_MEMORY_LIMIT,
            'max_execution_time' => ini_get('max_execution_time') . 's',
            'max_upload_size' => size_format(wp_max_upload_size()),
        );
    }

    /**
     * 🔌 Get Plugins Information
     */
    private static function get_plugins_info() {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $all_plugins = get_plugins();
        $active_plugins = get_option('active_plugins', array());
        
        $network_active = array();
        if (is_multisite()) {
            $network_active = get_site_option('active_sitewide_plugins', array());
        }

        $plugin_updates = get_site_transient('update_plugins');

        $plugins = array();

        foreach ($all_plugins as $plugin_path => $plugin_data) {
            $is_active = in_array($plugin_path, $active_plugins) || isset($network_active[$plugin_path]);
            $has_update = isset($plugin_updates->response[$plugin_path]);

            $plugins[] = array(
                'id' => $plugin_path,  // 👈 ADDED: Plugin path (directory/file.php)
                'name' => $plugin_data['Name'],
                'version' => $plugin_data['Version'],
                'author' => wp_strip_all_tags($plugin_data['Author']),
                'active' => $is_active,
                'update_available' => $has_update,
                'new_version' => $has_update ? $plugin_updates->response[$plugin_path]->new_version : null,
            );
        }

        $active_count = count($active_plugins) + count($network_active);

        return array(
            'total' => count($all_plugins),
            'active' => $active_count,
            'inactive' => count($all_plugins) - $active_count,
            'updates_available' => isset($plugin_updates->response) ? count($plugin_updates->response) : 0,
            'list' => $plugins,
        );
    }

    /**
     * 🎨 Get Themes Information
     */
    private static function get_themes_info() {
        $all_themes = wp_get_themes();
        $current_theme = wp_get_theme();
        $theme_updates = get_site_transient('update_themes');

        $themes = array();

        foreach ($all_themes as $theme_slug => $theme) {
            $is_active = ($theme->get_stylesheet() === $current_theme->get_stylesheet());
            $has_update = isset($theme_updates->response[$theme_slug]);

            $themes[] = array(
                'id' => $theme->get_stylesheet(),  // 👈 ADDED: Theme stylesheet (directory name)
                'name' => $theme->get('Name'),
                'version' => $theme->get('Version'),
                'author' => wp_strip_all_tags($theme->get('Author')),
                'active' => $is_active,
                'is_child_theme' => !empty($theme->parent()),
                'update_available' => $has_update,
                'new_version' => $has_update ? $theme_updates->response[$theme_slug]['new_version'] : null,
            );
        }

        return array(
            'total' => count($all_themes),
            'active_theme' => $current_theme->get('Name'),
            'active_theme_id' => $current_theme->get_stylesheet(),  // 👈 ADDED: Active theme ID
            'active_version' => $current_theme->get('Version'),
            'updates_available' => isset($theme_updates->response) ? count($theme_updates->response) : 0,
            'list' => $themes,
        );
    }

    /**
     * 👥 Get Users Information
     */
    private static function get_users_info() {
        $total_users = count_users();
        
        $thirty_days_ago = date('Y-m-d H:i:s', strtotime('-30 days'));
        $recent_users_query = new \WP_User_Query(array(
            'date_query' => array(
                array(
                    'after' => $thirty_days_ago,
                    'inclusive' => true,
                ),
            ),
            'fields' => 'ID',
            'count_total' => true,
        ));

        return array(
            'total' => $total_users['total_users'],
            'roles' => $total_users['avail_roles'],
            'recent_30_days' => $recent_users_query->get_total(),
        );
    }

    /**
     * 🗄️ Get Database Information
     */
    private static function get_database_info() {
        global $wpdb;

        $db_size = $wpdb->get_var($wpdb->prepare("
            SELECT SUM(data_length + index_length) 
            FROM information_schema.TABLES 
            WHERE table_schema = %s
        ", DB_NAME));

        $table_count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM information_schema.TABLES 
            WHERE table_schema = %s
        ", DB_NAME));

        $largest_tables = $wpdb->get_results($wpdb->prepare("
            SELECT 
                table_name,
                ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
            FROM information_schema.TABLES
            WHERE table_schema = %s
            ORDER BY (data_length + index_length) DESC
            LIMIT 5
        ", DB_NAME), ARRAY_A);

        $mysql_version = $wpdb->get_var("SELECT VERSION()");

        return array(
            'name' => DB_NAME,
            'prefix' => $wpdb->prefix,
            'size_mb' => round($db_size / 1024 / 1024, 2),
            'table_count' => (int)$table_count,
            'mysql_version' => $mysql_version,
            'largest_tables' => $largest_tables,
        );
    }

    /**
     * 🔒 Get Security Information
     */
    private static function get_security_info() {
        $debug_log_path = WP_CONTENT_DIR . '/debug.log';
        $debug_log_exists = file_exists($debug_log_path);
        $debug_log_size = 0;
        
        if ($debug_log_exists && is_readable($debug_log_path)) {
            $debug_log_size = filesize($debug_log_path);
        }

        $htaccess_path = ABSPATH . '.htaccess';
        $htaccess_exists = file_exists($htaccess_path);

        return array(
            'https_enabled' => is_ssl(),
            'file_editing_disabled' => defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT,
            'debug_log_exists' => $debug_log_exists,
            'debug_log_size_mb' => round($debug_log_size / 1024 / 1024, 2),
            'htaccess_exists' => $htaccess_exists,
            'htaccess_writable' => $htaccess_exists && is_writable($htaccess_path),
            'xmlrpc_enabled' => apply_filters('xmlrpc_enabled', true),
        );
    }

    /**
     * ⚡ Get Performance Information
     */
    private static function get_performance_info() {
        global $wpdb;

        $object_cache_enabled = wp_using_ext_object_cache();

        $autoload_size = $wpdb->get_var("
            SELECT SUM(LENGTH(option_value)) 
            FROM {$wpdb->options} 
            WHERE autoload = 'yes'
        ");

        $transients_count = $wpdb->get_var("
            SELECT COUNT(*) 
            FROM {$wpdb->options} 
            WHERE option_name LIKE '%_transient_%'
        ");

        $cron_jobs = _get_cron_array();
        $cron_count = 0;
        $next_cron = null;
        
        if (!empty($cron_jobs) && is_array($cron_jobs)) {
            foreach ($cron_jobs as $timestamp => $cron) {
                if (is_array($cron)) {
                    $cron_count += count($cron);
                }
            }
            $next_cron = min(array_keys($cron_jobs));
        }

        $opcache_status = false;
        if (function_exists('opcache_get_status')) {
            $status = @opcache_get_status(false);
            $opcache_status = !empty($status['opcache_enabled']);
        }

        return array(
            'object_cache_enabled' => $object_cache_enabled,
            'autoload_size_mb' => round($autoload_size / 1024 / 1024, 2),
            'transients_count' => (int)$transients_count,
            'cron_jobs_count' => $cron_count,
            'next_cron_scheduled' => $next_cron ? date('Y-m-d H:i:s', $next_cron) : null,
            'opcache_enabled' => $opcache_status,
        );
    }

    /**
     * 📁 Get Media Information
     */
    private static function get_media_info() {
        $attachments = wp_count_posts('attachment');
        $upload_dir = wp_upload_dir();

        return array(
            'total_attachments' => isset($attachments->inherit) ? (int)$attachments->inherit : 0,
            'uploads_dir' => $upload_dir['basedir'],
            'uploads_writable' => is_writable($upload_dir['basedir']),
            'uploads_url' => $upload_dir['baseurl'],
        );
    }

    /**
     * 🔄 Get Updates Information
     */
    private static function get_updates_info() {
        // 👈 FIXED: Added \ prefix for global namespace
        $core_updates = \get_core_updates();
        $plugin_updates = get_site_transient('update_plugins');
        $theme_updates = get_site_transient('update_themes');

        $core_update_available = false;
        $core_new_version = null;

        if (!empty($core_updates) && is_array($core_updates)) {
            if (isset($core_updates[0]->response) && $core_updates[0]->response === 'upgrade') {
                $core_update_available = true;
                $core_new_version = $core_updates[0]->version ?? null;
            }
        }

        return array(
            'core_update_available' => $core_update_available,
            'core_new_version' => $core_new_version,
            'plugin_updates_count' => isset($plugin_updates->response) ? count($plugin_updates->response) : 0,
            'theme_updates_count' => isset($theme_updates->response) ? count($theme_updates->response) : 0,
        );
    }

    /**
     * 🖥️ Get Server Environment Information
     */
    private static function get_server_info() {
        $curl_info = 'Not installed';
        if (function_exists('curl_version')) {
            $curl = curl_version();
            $curl_info = $curl['version'] ?? 'Unknown';
        }

        $mysql_client = 'Unknown';
        if (function_exists('mysqli_get_client_info')) {
            $mysql_client = mysqli_get_client_info();
        }

        return array(
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'server_os' => PHP_OS,
            'curl_version' => $curl_info,
            'openssl_version' => OPENSSL_VERSION_TEXT ?? 'Unknown',
            'mysql_client_version' => $mysql_client,
            'extensions' => array(
                'curl' => extension_loaded('curl'),
                'gd' => extension_loaded('gd'),
                'imagick' => extension_loaded('imagick'),
                'mbstring' => extension_loaded('mbstring'),
                'openssl' => extension_loaded('openssl'),
                'xml' => extension_loaded('xml'),
                'zip' => extension_loaded('zip'),
            ),
        );
    }
}