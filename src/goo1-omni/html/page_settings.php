<?php
/**
 * goo1 Omni Settings Page
 * Modern WordPress Admin Interface
 */

use \plugins\goo1\omni\config;

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Initialize variables
$is_saved = false;
$errors = array();

// Handle form submission
if (!empty($_POST["act"]) && $_POST["act"] == "save") {
    // Verify nonce
    if (!isset($_POST['goo1_omni_nonce']) || !wp_verify_nonce($_POST['goo1_omni_nonce'], 'goo1_omni_settings')) {
        $errors[] = __('Security verification failed. Please try again.', 'goo1-omni');
    } else {
        // Sanitize and save Cloudflare countries
        if (isset($_POST["cloudflare_countriesadmin"])) {
            $countries = preg_replace("@[^A-Z,]+@", "", strtoupper(sanitize_text_field($_POST["cloudflare_countriesadmin"])));
            config::set("cloudflare_admin_country", $countries);
        }
        
        // Save static header setting
        config::set("page_is_static_header", !empty($_POST["page_is_static_header"]));
        
        $is_saved = true;
    }
}

// Get current tab
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';

?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php
    // Display success notice
    if ($is_saved) {
        echo '<div class="notice notice-success is-dismissible"><p><span class="dashicons dashicons-yes-alt"></span> ' . esc_html__('Settings saved successfully.', 'goo1-omni') . '</p></div>';
    }
    
    // Display errors
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($error) . '</p></div>';
        }
    }
    ?>
    
    <!-- Tabs Navigation -->
    <nav class="nav-tab-wrapper wp-clearfix" aria-label="Secondary menu">
        <a href="?page=goo1omni-settings&tab=dashboard" class="nav-tab <?php echo $active_tab == 'dashboard' ? 'nav-tab-active' : ''; ?>">
            <span class="dashicons dashicons-dashboard"></span> <?php _e('Dashboard', 'goo1-omni'); ?>
        </a>
        <a href="?page=goo1omni-settings&tab=security" class="nav-tab <?php echo $active_tab == 'security' ? 'nav-tab-active' : ''; ?>">
            <span class="dashicons dashicons-shield"></span> <?php _e('Security', 'goo1-omni'); ?>
        </a>
        <a href="?page=goo1omni-settings&tab=developer" class="nav-tab <?php echo $active_tab == 'developer' ? 'nav-tab-active' : ''; ?>">
            <span class="dashicons dashicons-editor-code"></span> <?php _e('Developer', 'goo1-omni'); ?>
        </a>
        <a href="?page=goo1omni-settings&tab=plugins" class="nav-tab <?php echo $active_tab == 'plugins' ? 'nav-tab-active' : ''; ?>">
            <span class="dashicons dashicons-admin-plugins"></span> <?php _e('Plugins', 'goo1-omni'); ?>
        </a>
        <a href="?page=goo1omni-settings&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">
            <span class="dashicons dashicons-admin-settings"></span> <?php _e('Settings', 'goo1-omni'); ?>
        </a>
        <a href="?page=goo1omni-settings&tab=system" class="nav-tab <?php echo $active_tab == 'system' ? 'nav-tab-active' : ''; ?>">
            <span class="dashicons dashicons-info"></span> <?php _e('System Info', 'goo1-omni'); ?>
        </a>
    </nav>
    
    <div class="tab-content">
        <?php
        switch ($active_tab) {
            case 'dashboard':
                render_dashboard_tab();
                break;
            case 'security':
                render_security_tab();
                break;
            case 'developer':
                render_developer_tab();
                break;
            case 'plugins':
                render_plugins_tab();
                break;
            case 'settings':
                render_settings_tab();
                break;
            case 'system':
                render_system_tab();
                break;
        }
        ?>
    </div>
</div>

<style>
.goo1-omni-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.goo1-omni-card h2 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #e5e5e5;
}

.goo1-status-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.goo1-status-box {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    text-align: center;
}

.goo1-status-box.success {
    border-left: 4px solid #46b450;
}

.goo1-status-box.warning {
    border-left: 4px solid #ffb900;
}

.goo1-status-box.error {
    border-left: 4px solid #dc3232;
}

.goo1-status-icon {
    font-size: 48px;
    margin-bottom: 10px;
}

.goo1-status-icon.success { color: #46b450; }
.goo1-status-icon.warning { color: #ffb900; }
.goo1-status-icon.error { color: #dc3232; }

.plugin-status-table {
    width: 100%;
    border-collapse: collapse;
}

.plugin-status-table th {
    text-align: left;
    padding: 10px;
    background: #f6f7f7;
    border-bottom: 1px solid #ccd0d4;
}

.plugin-status-table td {
    padding: 12px 10px;
    border-bottom: 1px solid #e5e5e5;
}

.plugin-status-table tr:last-child td {
    border-bottom: none;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.status-badge.active {
    background: #d7f7dc;
    color: #1e4620;
}

.status-badge.inactive {
    background: #f7dcdc;
    color: #761919;
}

.nav-tab .dashicons {
    line-height: inherit;
    vertical-align: text-bottom;
}
</style>

<?php

/**
 * Render Dashboard Tab
 */
function render_dashboard_tab() {
    ?>
    <div class="goo1-omni-card">
        <h2><?php _e('System Status Overview', 'goo1-omni'); ?></h2>
        
        <div class="goo1-status-grid">
            <!-- Cloudflare Status -->
            <div class="goo1-status-box <?php echo !empty($_SERVER["HTTP_CF_RAY"]) ? 'success' : 'warning'; ?>">
                <div class="goo1-status-icon <?php echo !empty($_SERVER["HTTP_CF_RAY"]) ? 'success' : 'warning'; ?>">
                    <span class="dashicons dashicons-<?php echo !empty($_SERVER["HTTP_CF_RAY"]) ? 'cloud-saved' : 'cloud'; ?>"></span>
                </div>
                <h3><?php _e('Cloudflare CDN', 'goo1-omni'); ?></h3>
                <p>
                    <?php 
                    if (!empty($_SERVER["HTTP_CF_RAY"])) {
                        _e('Active and routing traffic', 'goo1-omni');
                        if (!empty($_SERVER["HTTP_CF_IPCOUNTRY"])) {
                            echo '<br><small>' . sprintf(__('Country: %s', 'goo1-omni'), esc_html($_SERVER["HTTP_CF_IPCOUNTRY"])) . '</small>';
                        }
                    } else {
                        _e('Not detected', 'goo1-omni');
                    }
                    ?>
                </p>
            </div>
            
            <!-- WordPress Version -->
            <div class="goo1-status-box success">
                <div class="goo1-status-icon success">
                    <span class="dashicons dashicons-wordpress-alt"></span>
                </div>
                <h3><?php _e('WordPress', 'goo1-omni'); ?></h3>
                <p><?php echo esc_html(get_bloginfo('version')); ?></p>
            </div>
            
            <!-- Plugin Health -->
            <div class="goo1-status-box <?php echo get_plugin_health_status(); ?>">
                <div class="goo1-status-icon <?php echo get_plugin_health_status(); ?>">
                    <span class="dashicons dashicons-admin-plugins"></span>
                </div>
                <h3><?php _e('Plugin Health', 'goo1-omni'); ?></h3>
                <p><?php echo get_plugin_health_text(); ?></p>
            </div>
        </div>
    </div>
    
    <div class="goo1-omni-card">
        <h2><?php _e('Quick Actions', 'goo1-omni'); ?></h2>
        <p>
            <a href="?page=goo1-omni-settings&tab=plugins" class="button button-primary">
                <span class="dashicons dashicons-admin-plugins"></span> <?php _e('Manage Plugins', 'goo1-omni'); ?>
            </a>
            <a href="<?php echo admin_url('options-general.php'); ?>" class="button">
                <span class="dashicons dashicons-admin-generic"></span> <?php _e('WordPress Settings', 'goo1-omni'); ?>
            </a>
            <a href="<?php echo admin_url('tools.php'); ?>" class="button">
                <span class="dashicons dashicons-admin-tools"></span> <?php _e('Tools', 'goo1-omni'); ?>
            </a>
        </p>
    </div>
    <?php
}

/**
 * Render Plugins Tab
 */
function render_plugins_tab() {
    $required_plugins = get_required_plugins();
    $recommended_plugins = get_recommended_plugins();
    ?>
    
    <div class="goo1-omni-card">
        <h2><?php _e('Required Plugins', 'goo1-omni'); ?></h2>
        <p class="description"><?php _e('These plugins are essential for optimal functionality.', 'goo1-omni'); ?></p>
        
        <table class="plugin-status-table">
            <thead>
                <tr>
                    <th><?php _e('Plugin', 'goo1-omni'); ?></th>
                    <th><?php _e('Status', 'goo1-omni'); ?></th>
                    <th><?php _e('Action', 'goo1-omni'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($required_plugins as $plugin): ?>
                    <?php render_plugin_row($plugin); ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="goo1-omni-card">
        <h2><?php _e('Recommended Plugins', 'goo1-omni'); ?></h2>
        <p class="description"><?php _e('These plugins enhance your site\'s functionality.', 'goo1-omni'); ?></p>
        
        <table class="plugin-status-table">
            <thead>
                <tr>
                    <th><?php _e('Plugin', 'goo1-omni'); ?></th>
                    <th><?php _e('Status', 'goo1-omni'); ?></th>
                    <th><?php _e('Action', 'goo1-omni'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recommended_plugins as $plugin): ?>
                    <?php render_plugin_row($plugin); ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

/**
 * Render Settings Tab
 */
function render_settings_tab() {
    ?>
    <form method="POST" action="">
        <?php wp_nonce_field('goo1_omni_settings', 'goo1_omni_nonce'); ?>
        <input type="hidden" name="act" value="save"/>
        
        <div class="goo1-omni-card">
            <h2><?php _e('Security Settings', 'goo1-omni'); ?></h2>
            
            <table class="form-table" role="presentation">
                <tbody>
                    <?php if (!empty($_SERVER["HTTP_CF_IPCOUNTRY"])): ?>
                    <tr>
                        <th scope="row">
                            <label for="fld_cloudflare_countriesadmin">
                                <?php _e('Allowed Countries', 'goo1-omni'); ?>
                            </label>
                        </th>
                        <td>
                            <input name="cloudflare_countriesadmin" 
                                   type="text" 
                                   id="fld_cloudflare_countriesadmin" 
                                   value="<?php echo esc_attr(config::get("cloudflare_admin_country") ?? ""); ?>" 
                                   class="regular-text"
                                   placeholder="US,GB,DE"/>
                            <p class="description">
                                <?php _e('Enter 2-letter country codes separated by commas (e.g., US,GB,DE). Leave empty to allow all countries.', 'goo1-omni'); ?>
                            </p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <tr>
                        <th scope="row"><?php _e('Allowed Countries', 'goo1-omni'); ?></th>
                        <td>
                            <p class="description">
                                <span class="dashicons dashicons-warning"></span>
                                <?php _e('Cloudflare CDN not detected. This feature requires Cloudflare.', 'goo1-omni'); ?>
                            </p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="goo1-omni-card">
            <h2><?php _e('Performance Settings', 'goo1-omni'); ?></h2>
            
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">
                            <?php _e('Static Page Mode', 'goo1-omni'); ?>
                        </th>
                        <td>
                            <fieldset>
                                <label>
                                    <input name="page_is_static_header" 
                                           type="checkbox" 
                                           id="fld_page_is_static_header" 
                                           value="1" 
                                           <?php checked(config::get("page_is_static_header"), true); ?>/>
                                    <?php _e('Enable static page mode', 'goo1-omni'); ?>
                                </label>
                                <p class="description">
                                    <?php _e('Improves page speed by serving static content. Disable if you frequently update your site.', 'goo1-omni'); ?>
                                </p>
                            </fieldset>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <p class="submit">
            <button type="submit" class="button button-primary">
                <span class="dashicons dashicons-yes"></span> <?php _e('Save Changes', 'goo1-omni'); ?>
            </button>
        </p>
    </form>
    <?php
}

/**
 * Render System Info Tab
 */
function render_system_tab() {
    global $wp_version;
    ?>
    <div class="goo1-omni-card">
        <h2><?php _e('System Information', 'goo1-omni'); ?></h2>
        
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><?php _e('WordPress Version', 'goo1-omni'); ?></th>
                    <td><?php echo esc_html($wp_version); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('PHP Version', 'goo1-omni'); ?></th>
                    <td><?php echo esc_html(phpversion()); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Server Software', 'goo1-omni'); ?></th>
                    <td><?php echo esc_html($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('MySQL Version', 'goo1-omni'); ?></th>
                    <td><?php echo esc_html($GLOBALS['wpdb']->db_version()); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Max Upload Size', 'goo1-omni'); ?></th>
                    <td><?php echo esc_html(size_format(wp_max_upload_size())); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Memory Limit', 'goo1-omni'); ?></th>
                    <td><?php echo esc_html(WP_MEMORY_LIMIT); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Active Theme', 'goo1-omni'); ?></th>
                    <td><?php echo esc_html(wp_get_theme()->get('Name')); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Multisite', 'goo1-omni'); ?></th>
                    <td><?php echo is_multisite() ? __('Yes', 'goo1-omni') : __('No', 'goo1-omni'); ?></td>
                </tr>
            </tbody>
        </table>
        
        <p>
            <button type="button" class="button" onclick="copySystemInfo()">
                <span class="dashicons dashicons-clipboard"></span> <?php _e('Copy System Info', 'goo1-omni'); ?>
            </button>
        </p>
    </div>
    
    <script>
    function copySystemInfo() {
        const table = document.querySelector('.form-table');
        let text = 'System Information:\n\n';
        
        table.querySelectorAll('tr').forEach(row => {
            const th = row.querySelector('th');
            const td = row.querySelector('td');
            if (th && td) {
                text += th.textContent + ': ' + td.textContent + '\n';
            }
        });
        
        navigator.clipboard.writeText(text).then(() => {
            alert('<?php _e('System information copied to clipboard!', 'goo1-omni'); ?>');
        });
    }
    </script>
    <?php
}

/**
 * Helper Functions
 */

function get_required_plugins() {
    $plugins = array();
    
    // Only show Elementor Pro if Elementor is active
    if (is_plugin_active("elementor/elementor.php")) {
        $plugins[] = array(
            'slug' => 'elementor-pro',
            'file' => 'elementor-pro/elementor-pro.php',
            'name' => 'Elementor Pro'
        );
    }
    
    $plugins[] = array(
        'slug' => 'worker',
        'file' => 'worker/init.php',
        'name' => 'ManageWP Worker'
    );
    
    $plugins[] = array(
        'slug' => 'better-wp-security',
        'file' => 'better-wp-security/better-wp-security.php',
        'name' => 'iThemes Security'
    );
    
    $plugins[] = array(
        'slug' => 'updraftplus',
        'file' => 'updraftplus/updraftplus.php',
        'name' => 'UpdraftPlus Backup'
    );
    
    return $plugins;
}

function get_recommended_plugins() {
    return array(
        array('slug' => 'cloudflare-flexible-ssl', 'file' => 'cloudflare-flexible-ssl/cloudflare-flexible-ssl.php', 'name' => 'Cloudflare Flexible SSL'),
        array('slug' => 'better-search-replace', 'file' => 'better-search-replace/better-search-replace.php', 'name' => 'Better Search Replace'),
        array('slug' => 'credit-tracker', 'file' => 'credit-tracker/credit-tracker.php', 'name' => 'Credit Tracker'),
        array('slug' => 'contextual-related-posts', 'file' => 'contextual-related-posts/contextual-related-posts.php', 'name' => 'Contextual Related Posts'),
        array('slug' => 'complianz-gdpr', 'file' => 'complianz-gdpr/complianz-gdpr.php', 'name' => 'Complianz GDPR'),
        array('slug' => 'simply-static', 'file' => 'simply-static/simply-static.php', 'name' => 'Simply Static'),
        array('slug' => 'wp-admin-ui-customize', 'file' => 'wp-admin-ui-customize/wp-admin-ui-customize.php', 'name' => 'WP Admin UI Customize'),
        array('slug' => 'redirection', 'file' => 'redirection/redirection.php', 'name' => 'Redirection'),
    );
}

function render_plugin_row($plugin) {
    $is_active = is_plugin_active($plugin['file']);
    ?>
    <tr>
        <td><strong><?php echo esc_html($plugin['name']); ?></strong></td>
        <td>
            <span class="status-badge <?php echo $is_active ? 'active' : 'inactive'; ?>">
                <?php echo $is_active ? __('Active', 'goo1-omni') : __('Inactive', 'goo1-omni'); ?>
            </span>
        </td>
        <td>
            <?php if ($is_active): ?>
                <span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
                <?php _e('Installed', 'goo1-omni'); ?>
            <?php else: ?>
                <a href="<?php echo esc_url(url_install_plugin($plugin['slug'])); ?>" class="button button-small">
                    <span class="dashicons dashicons-download"></span> <?php _e('Install', 'goo1-omni'); ?>
                </a>
            <?php endif; ?>
        </td>
    </tr>
    <?php
}

function get_plugin_health_status() {
    $required = get_required_plugins();
    $active_count = 0;
    
    foreach ($required as $plugin) {
        if (is_plugin_active($plugin['file'])) {
            $active_count++;
        }
    }
    
    $percentage = (count($required) > 0) ? ($active_count / count($required)) * 100 : 100;
    
    if ($percentage == 100) return 'success';
    if ($percentage >= 75) return 'warning';
    return 'error';
}

function get_plugin_health_text() {
    $required = get_required_plugins();
    $active_count = 0;
    
    foreach ($required as $plugin) {
        if (is_plugin_active($plugin['file'])) {
            $active_count++;
        }
    }
    
    return sprintf(
        __('%d of %d required plugins active', 'goo1-omni'),
        $active_count,
        count($required)
    );
}

function url_install_plugin($slug) {
    $action = 'install-plugin';
    return wp_nonce_url(
        add_query_arg(
            array(
                'action' => $action,
                'plugin' => $slug
            ),
            admin_url('update.php')
        ),
        $action . '_' . $slug
    );
}

/**
 * Render Security Tab
 */
function render_security_tab() {
    $last_check = \plugins\goo1\omni\FileIntegrity::get_last_check();
    
    // Handle manual check trigger
    if (isset($_GET['action']) && $_GET['action'] === 'run_integrity_check') {
        if (isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'run_integrity_check')) {
            $last_check = \plugins\goo1\omni\FileIntegrity::force_check();
            echo '<div class="notice notice-success is-dismissible"><p>' . __('File integrity check completed.', 'goo1-omni') . '</p></div>';
        }
    }
    
    ?>
    <div class="goo1-omni-card">
        <h2><?php _e('File Integrity Monitoring', 'goo1-omni'); ?></h2>
        <p class="description">
            <?php _e('Monitors WordPress core files for unauthorized modifications. Checks are performed daily automatically.', 'goo1-omni'); ?>
        </p>
        
        <?php if ($last_check): ?>
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row"><?php _e('Last Check', 'goo1-omni'); ?></th>
                        <td><?php echo esc_html($last_check['checked_at']); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('WordPress Version', 'goo1-omni'); ?></th>
                        <td><?php echo esc_html($last_check['wp_version']); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Status', 'goo1-omni'); ?></th>
                        <td>
                            <?php if ($last_check['status'] === 'clean'): ?>
                                <span class="status-badge active">
                                    <span class="dashicons dashicons-yes-alt"></span> <?php _e('Clean', 'goo1-omni'); ?>
                                </span>
                            <?php elseif ($last_check['status'] === 'warning'): ?>
                                <span class="status-badge" style="background: #fff3cd; color: #856404;">
                                    <span class="dashicons dashicons-warning"></span> <?php _e('Warning', 'goo1-omni'); ?>
                                </span>
                            <?php else: ?>
                                <span class="status-badge inactive">
                                    <span class="dashicons dashicons-dismiss"></span> <?php _e('Compromised', 'goo1-omni'); ?>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <?php if (!empty($last_check['modified_files'])): ?>
                <h3><?php _e('Modified Core Files', 'goo1-omni'); ?></h3>
                <ul style="background: #fff; padding: 15px; border-left: 4px solid #dc3232;">
                    <?php foreach ($last_check['modified_files'] as $file): ?>
                        <li><code><?php echo esc_html($file['file']); ?></code></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            
            <?php if (!empty($last_check['missing_files'])): ?>
                <h3><?php _e('Missing Core Files', 'goo1-omni'); ?></h3>
                <ul style="background: #fff; padding: 15px; border-left: 4px solid #dc3232;">
                    <?php foreach ($last_check['missing_files'] as $file): ?>
                        <li><code><?php echo esc_html($file); ?></code></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            
            <?php if (!empty($last_check['unknown_files'])): ?>
                <h3><?php _e('Unknown Files in Core Directories', 'goo1-omni'); ?></h3>
                <p class="description"><?php _e('These files were found in wp-admin or wp-includes but are not part of WordPress core.', 'goo1-omni'); ?></p>
                <ul style="background: #fff; padding: 15px; border-left: 4px solid #ffb900;">
                    <?php 
                    $count = 0;
                    foreach ($last_check['unknown_files'] as $file): 
                        if ($count++ < 20):
                    ?>
                        <li><code><?php echo esc_html($file); ?></code></li>
                    <?php 
                        endif;
                    endforeach; 
                    if (count($last_check['unknown_files']) > 20):
                    ?>
                        <li><em><?php printf(__('... and %d more files', 'goo1-omni'), count($last_check['unknown_files']) - 20); ?></em></li>
                    <?php endif; ?>
                </ul>
            <?php endif; ?>
        <?php else: ?>
            <p><?php _e('No integrity check has been performed yet.', 'goo1-omni'); ?></p>
        <?php endif; ?>
        
        <p>
            <a href="<?php echo wp_nonce_url(add_query_arg(array('page' => 'goo1omni-settings', 'tab' => 'security', 'action' => 'run_integrity_check'), admin_url('options-general.php')), 'run_integrity_check'); ?>" class="button button-primary">
                <span class="dashicons dashicons-update"></span> <?php _e('Run Check Now', 'goo1-omni'); ?>
            </a>
        </p>
    </div>
    
    <div class="goo1-omni-card">
        <h2><?php _e('Security Features', 'goo1-omni'); ?></h2>
        
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><?php _e('XML-RPC', 'goo1-omni'); ?></th>
                    <td>
                        <span class="status-badge inactive">
                            <span class="dashicons dashicons-dismiss"></span> <?php _e('Disabled', 'goo1-omni'); ?>
                        </span>
                        <p class="description"><?php _e('XML-RPC is disabled by default for security. This prevents brute-force attacks.', 'goo1-omni'); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php
}

/**
 * Render Developer Tab
 */
function render_developer_tab() {
    $is_debug_enabled = \plugins\goo1\omni\DebugMode::is_debug_enabled();
    $is_writable = \plugins\goo1\omni\DebugMode::is_wp_config_writable();
    $debug_log_exists = \plugins\goo1\omni\DebugMode::debug_log_exists();
    $debug_log_size = \plugins\goo1\omni\DebugMode::get_debug_log_size();
    
    // Show toggle result message
    if (isset($_GET['debug_toggled'])) {
        $status = sanitize_text_field($_GET['debug_toggled']);
        $message = $status === 'enabled' 
            ? __('Debug mode has been enabled.', 'goo1-omni')
            : __('Debug mode has been disabled.', 'goo1-omni');
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
    }
    
    // Handle clear log action
    if (isset($_GET['action']) && $_GET['action'] === 'clear_debug_log') {
        if (isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'clear_debug_log')) {
            \plugins\goo1\omni\DebugMode::clear_debug_log();
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Debug log cleared.', 'goo1-omni') . '</p></div>';
            $debug_log_exists = false;
            $debug_log_size = 0;
        }
    }
    
    ?>
    <div class="goo1-omni-card">
        <h2><?php _e('Debug Mode', 'goo1-omni'); ?></h2>
        <p class="description">
            <?php _e('Quick toggle for WP_DEBUG and related debugging constants in wp-config.php', 'goo1-omni'); ?>
        </p>
        
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><?php _e('Debug Status', 'goo1-omni'); ?></th>
                    <td>
                        <?php if ($is_debug_enabled): ?>
                            <span class="status-badge" style="background: #fff3cd; color: #856404;">
                                <span class="dashicons dashicons-warning"></span> <?php _e('Enabled', 'goo1-omni'); ?>
                            </span>
                        <?php else: ?>
                            <span class="status-badge active">
                                <span class="dashicons dashicons-yes-alt"></span> <?php _e('Disabled', 'goo1-omni'); ?>
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('WP_DEBUG', 'goo1-omni'); ?></th>
                    <td><?php echo $is_debug_enabled ? '<strong>true</strong>' : 'false'; ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('WP_DEBUG_LOG', 'goo1-omni'); ?></th>
                    <td><?php echo \plugins\goo1\omni\DebugMode::is_debug_log_enabled() ? '<strong>true</strong>' : 'false'; ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('WP_DEBUG_DISPLAY', 'goo1-omni'); ?></th>
                    <td><?php echo \plugins\goo1\omni\DebugMode::is_debug_display_enabled() ? '<strong>true</strong>' : 'false'; ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('wp-config.php', 'goo1-omni'); ?></th>
                    <td>
                        <?php if ($is_writable): ?>
                            <span class="status-badge active">
                                <span class="dashicons dashicons-yes-alt"></span> <?php _e('Writable', 'goo1-omni'); ?>
                            </span>
                        <?php else: ?>
                            <span class="status-badge inactive">
                                <span class="dashicons dashicons-dismiss"></span> <?php _e('Not Writable', 'goo1-omni'); ?>
                            </span>
                            <p class="description"><?php _e('wp-config.php must be writable to toggle debug mode.', 'goo1-omni'); ?></p>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <?php if ($is_writable): ?>
            <p>
                <a href="<?php echo wp_nonce_url(add_query_arg(array('goo1_debug_action' => 'toggle'), admin_url('options-general.php')), 'goo1_debug_toggle', 'goo1_debug_nonce'); ?>" 
                   class="button button-primary">
                    <?php if ($is_debug_enabled): ?>
                        <span class="dashicons dashicons-no"></span> <?php _e('Disable Debug Mode', 'goo1-omni'); ?>
                    <?php else: ?>
                        <span class="dashicons dashicons-yes"></span> <?php _e('Enable Debug Mode', 'goo1-omni'); ?>
                    <?php endif; ?>
                </a>
            </p>
        <?php else: ?>
            <div class="notice notice-warning inline">
                <p>
                    <span class="dashicons dashicons-warning"></span>
                    <?php _e('Cannot toggle debug mode: wp-config.php is not writable. Please check file permissions.', 'goo1-omni'); ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if ($debug_log_exists): ?>
    <div class="goo1-omni-card">
        <h2><?php _e('Debug Log', 'goo1-omni'); ?></h2>
        <p class="description">
            <?php printf(__('Log file size: %s', 'goo1-omni'), size_format($debug_log_size)); ?>
        </p>
        
        <div style="background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 4px; overflow-x: auto; max-height: 400px; font-family: 'Courier New', monospace; font-size: 12px;">
            <pre style="margin: 0; color: #d4d4d4;"><?php echo esc_html(\plugins\goo1\omni\DebugMode::get_debug_log_tail(50)); ?></pre>
        </div>
        
        <p style="margin-top: 15px;">
            <a href="<?php echo wp_nonce_url(add_query_arg(array('page' => 'goo1omni-settings', 'tab' => 'developer', 'action' => 'clear_debug_log'), admin_url('options-general.php')), 'clear_debug_log'); ?>" 
               class="button"
               onclick="return confirm('<?php esc_attr_e('Are you sure you want to clear the debug log?', 'goo1-omni'); ?>');">
                <span class="dashicons dashicons-trash"></span> <?php _e('Clear Log', 'goo1-omni'); ?>
            </a>
        </p>
    </div>
    <?php endif; ?>
    <?php
}