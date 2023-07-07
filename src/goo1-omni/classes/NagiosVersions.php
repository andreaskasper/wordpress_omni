<?php

namespace plugins\goo1\omni;

class NagiosVersions {

    public static function test() {
        global $wp_version;

        require_once(__DIR__."/Nagios.php");

        
        $core_updates = false;
        $plugin_updates = false;

        wp_version_check();
        wp_update_plugins();
        wp_update_themes();

if (function_exists('get_transient')) {
    $core = get_transient('update_core');
    $plugins = get_transient('update_plugins');
    $themes = get_transient('update_themes');

    if ($core == false) {
        $core = get_site_transient('update_core');
        $plugins = get_site_transient('update_plugins');
        $themes = get_site_transient('update_themes');
    }
} else {
    $core = get_site_transient('update_core');
    $plugins = get_site_transient('update_plugins');
    $themes = get_site_transient('update_themes');
}

$core_available = false;
$plugin_available = false;
$theme_available = false;

foreach ($core->updates as $core_update) {
    if ($core_update->current != $wp_version) {
        $core_available = true;
    }
}

$plugin_available = (count($plugins->response) > 0);
$theme_available = (count($themes->response) > 0);

// Get version of latest Wordpress
$latest_version = $core_update->current;

// Get version of current Wordpress
$current_version = $wp_version;

$text = array();

if ($core_available) {
    $text[] = 'Current version is (' . $current_version  . '). Update to Wordpress ' . $latest_version  . ' available.';
}

if ($plugin_available) {
    $text[] = 'Plugin updates available.';
}

        if ($theme_available) {
            $text[] = 'Theme updates available.';
        }

        $status = 'Current version is (' . $current_version  .  '). No core, plugin or theme updates available.';

        if ($core_available) {
            $status = 'CRITICAL';
            $state = 2;
        } elseif ($theme_available or $plugin_available) {
            $status = 'WARNING';
            $state = 1;
        } else {
            $state = 0;    
        }

        if (!is_plugin_active("wordfence/wordfence.php") AND !is_plugin_active("better-wp-security/better-wp-security.php")) {
            $status = 'WARNING';
            $state = 1;
            $text[] = 'No Application Firewall found! Install Wordfence or iThemes Security.';
        }

        Nagios::send($state, $status . '#' . implode(";", $text));
        exit;
    }


}
