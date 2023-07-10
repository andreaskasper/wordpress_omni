<?php

/*
 * Description for SiteHealth:
 * https://make.wordpress.org/core/2019/04/25/site-health-check-in-5-2/
 * 
 * 
 * 
 * 
 */

namespace plugins\goo1\omni;

class SiteHealth {

    public static function init($tests) {
        $tests["direct"]["goo1_cloudflare_active"] = array(
            "label" => __( "CDN Status" ),
            "test"  => [__CLASS__, "test_cloudflare_active"],
        );
        $tests["direct"]["goo1_blog_public"] = array(
            "label" => __( "Blog public" ),
            "test"  => [__CLASS__, "test_blog_public"],
        );
        $tests["direct"]["goo1_blog_diskspace"] = array(
            "label" => __( "Diskspace" ),
            "test"  => [__CLASS__, "test_diskspace"],
        );
        $tests["direct"]["goo1_application_firewall"] = array(
            "label" => __( "Application Firewall" ),
            "test"  => [__CLASS__, "test_application_firewall"],
        );
        $tests["direct"]["goo1_plugins_worker"] = array(
            "label" => __( "ManageWP" ),
            "test"  => [__CLASS__, "test_plugins_worker"],
        );
        $tests["direct"]["goo1_plugins_elementor"] = array(
            "label" => __( "Elementor" ),
            "test"  => [__CLASS__, "test_plugins_elementor"],
        );
        $tests["direct"]["goo1_plugins_updraftplus"] = array(
            "label" => __( "Updraft Plus" ),
            "test"  => [__CLASS__, "test_plugins_updraftplus"],
        );
        $tests["direct"]["goo1_blog_comments"] = array(
            "label" => __( "Comments" ),
            "test"  => [__CLASS__, "test_blog_comments"],
        );
        $tests["direct"]["goo1_nagios_lastused"] = array(
            "label" => __( "Nagios Last Used" ),
            "test"  => [__CLASS__, "test_nagios_lastused"],
        );
        return $tests;
    }

    public static function test_cloudflare_active() {
        $result = array(
            'label'       => __( 'CDN is running' ),
            'status'      => 'good',
            'badge'       => array(
                'label' => __( 'CDN' ),
                'color' => 'blue',
            ),
            'description' => '<p>The page can be served much much faster, if you use a CDN.</p>',
            'actions'     => '',
            'test'        => 'goo1_cloudflare_active',
        );
    
        if (!isset($_SERVER["HTTP_CF_RAY"])) {
            $result['status'] = 'recommended';
            $result['label'] = __( 'CDN is NOT running' );
            $result['description'] = '<p>To improve the pagespeed please consider using a Content-Distribution-Network</p><p>Contact Andreas for help</p>';
        }
    
        //$result['actions'] .= var_export($_SERVER, true);
        return $result;
    }

    public static function test_blog_public() {
        $result = array(
            'label'       => __( 'Indexing allowed' ),
            'status'      => 'good',
            'badge'       => array(
                'label' => __( 'Google' ),
                'color' => 'blue',
            ),
            'description' => '<p>The page is public!</p>',
            'actions'     => '',
            'test'        => 'goo1_blog_public',
        );

        if( 0 == get_option( "blog_public" )){
            $result['status'] = 'recommended';
            $result['label'] = __( 'Blog is not public' );
            $result['description'] = '<p>Search Engines are not allowed to index the website. This is not good, if the website is public.</p>';
        }
        return $result;
    }

    public static function test_diskspace() {
        $ds = disk_total_space(__DIR__);
        $df = disk_free_space(__DIR__);

        $proz = 100-(100*$df/$ds);

        $result = array(
            'label'       => __( 'Enough space on the webserver. '.number_format($proz, 1).'% full' ),
            'status'      => 'good',
            'badge'       => array(
                'label' => __( 'SERVER' ),
                'color' => 'blue',
            ),
            'description' => '<p>You have enough space on the webserver. It\'s only '.$proz.'% filled.</p>',
            'actions'     => '',
            'test'        => 'goo1_blog_diskspace',
        );

        if ($df/$ds <= 0.2) {
            $result['status'] = 'recommended';
            $result['label'] = __( 'Less space on the webserver. '.number_format($proz, 1).'% full' );
        }

        if ($df/$ds <= 0.05) {
            $result['status'] = 'critical';
            $result['label'] = __( 'CRITICAL - Very less space on the webserver. '.number_format($proz, 1).'% full' );
        }
    
        return $result;
    }

    public static function test_application_firewall() {
        $result = array(
            'label'       => __( 'Application Firewall should be installed' ),
            'status'      => 'recommended',
            'badge'       => array(
                'label' => __( 'WORDPRESS' ),
                'color' => 'blue',
            ),
            'description' => '<p>You should install an application firewall to secure the wordpress page</p>',
            'actions'     => '',
            'test'        => 'goo1_application_firewall',
        );

        if (is_plugin_active("wordfence/wordfence.php")) {
            $result['status'] = 'good';
            $result['label'] = __( 'Application Firewall: Wordfence is installed' );
        }
        if (is_plugin_active("better-wp-security/better-wp-security.php")) {
            $result['status'] = 'good';
            $result['label'] = __( 'Application Firewall: iTheme Security is installed' );
        }
        return $result;
    }

    public static function test_plugins_worker() {
        global $wpdb;
        $result = array(
            'label'       => __( 'ManageWP is installed' ),
            'status'      => 'good',
            'badge'       => array(
                'label' => __( 'Plugins' ),
                'color' => 'blue',
            ),
            'description' => '<p></p>',
            'actions'     => '',
            'test'        => 'goo1_plugins_worker',
        );

        if (!is_plugin_active("worker/init.php")) {
            $result['status'] = 'recommended';
            $result['label'] = __( 'ManageWP should be installed' );
            $result["description"] = __("ManageWP helps you to manage all your Wordpress Pages from one instance.");
            return $result;
        }

        $row = $wpdb->get_row( "SELECT * FROM {$wpdb->options} WHERE option_name LIKE 'mwp_key_last_used_%' LIMIT 0,1", ARRAY_A );
        if (!empty($row["option_value"])) {
            if ($row["option_value"] > time()-7*86400) {
                $result["description"] = __("ManageWP helps you to manage all your Wordpress Pages from one instance. Last connection time ".date("Y-m-d H:i:s T", $row["option_value"]));
            } else {
                $result['status'] = 'recommended';
                $result['label'] = __( "ManageWP had no connection for 7 days" );
                $result["description"] = __("ManageWP wasn't connected to the server in the last 7 days. Please check the connection. Last connection time ".date("Y-m-d H:i:s T", $row["option_value"]));
            }
        }

        return $result;
    }

    public static function test_plugins_elementor() {
        $result = array(
            'label'       => __( 'Elementor okay!' ),
            'status'      => 'good',
            'badge'       => array(
                'label' => __( 'Plugins' ),
                'color' => 'blue',
            ),
            'description' => '<p></p>',
            'actions'     => '',
            'test'        => 'goo1_plugins_elementor',
        );

        if (!is_plugin_active("elementor/elementor.php")) {
            $result['status'] = 'good';
            $result['label'] = __( 'Elementor deactivated.' );
            $result["description"] = __("No checks are running, because Elementor is not installed or deactivated.");
            return $result;
        }

        if (empty(get_option( "elementor_font_awesome_pro_kit_id" ))) {
            $result['status'] = 'recommended';
            $result['label'] = __( 'No FontAwesome kit ID is set for Elementor' );
            $result["description"] = __("To enable all Icons you should add a FontAwesome kit id. Andreas can help you.");
            //$result["actions"] = '<a href="/wp-admin/options-general.php?page=updraftplus#updraft-navtab-settings-content">Updraft Auto-Backup Settings</a>';
            return $result;
        }

        if (get_option( "current_theme" ) != "Hello Elementor") {
            $result['status'] = 'recommended';
            $result['label'] = __( 'Hello Elementor Theme is not used' );
            $result["description"] = __("If you're using Elementor the Hello Elementor Theme is highly recommended. If you're using a child-theme of it, please ignore this message.");
            //$result["actions"] = '<a href="/wp-admin/options-general.php?page=updraftplus#updraft-navtab-settings-content">Updraft Auto-Backup Settings</a>';
            return $result;
        }

        return $result;
    }

    public static function test_plugins_updraftplus() {
        $result = array(
            'label'       => __( 'Updraft Plus okay!' ),
            'status'      => 'good',
            'badge'       => array(
                'label' => __( 'Plugins' ),
                'color' => 'blue',
            ),
            'description' => '<p></p>',
            'actions'     => '',
            'test'        => 'goo1_plugins_updraftplus',
        );

        if (!is_plugin_active("updraftplus/updraftplus.php")) {
            $result['status'] = 'recommended';
            $result['label'] = __( 'Updraft Plus should be installed' );
            $result["description"] = __("Updraft Plus helps you to backup your website in case you need it later.");
            return $result;
        }

        if (get_option( "updraft_service" ) != "dropbox") {
            $result['status'] = 'recommended';
            $result['label'] = __( 'Use a remote backup for Updraft Plus' );
            $result["description"] = __("You should save your backups remotely, so if the server crashes, you still have a backup.");
            $result["actions"] = '<a href="/wp-admin/options-general.php?page=updraftplus#updraft-navtab-settings-content">Updraft Auto-Backup Settings</a>';
            return $result;
        }

        if (get_option( "updraft_interval" ) == "manual") {
            $result['status'] = 'recommended';
            $result['label'] = __( 'Updraft Plus interval deactivated' );
            $result["description"] = __("The interval for the backup should be daily or weekly.");
            $result["actions"] = '<a href="/wp-admin/options-general.php?page=updraftplus#updraft-navtab-settings-content">Updraft Auto-Backup Settings</a>';
            return $result;
        }

        if (get_option( "updraft_retain" ) < 10) {
            $result['status'] = 'recommended';
            $result['label'] = __( 'Updraft Plus backups less than 10' );
            $result["description"] = __("You should add more retained backups to rollback at least 6 months.");
            $result["actions"] = '<a href="/wp-admin/options-general.php?page=updraftplus#updraft-navtab-settings-content">Updraft Auto-Backup Settings</a>';
            return $result;
        }

        if (get_option( "updraft_retain_db" ) < 10) {
            $result['status'] = 'recommended';
            $result['label'] = __( 'Updraft Plus DB backups less than 10' );
            $result["description"] = __("You should add more retained backups to rollback at least 6 months.");
            $result["actions"] = '<a href="/wp-admin/options-general.php?page=updraftplus#updraft-navtab-settings-content">Updraft Auto-Backup Settings</a>';
            return $result;
        }

        return $result;
    }

    public static function test_blog_comments() {
        $result = array(
            'label'       => __( 'Comments are okay on the blog' ),
            'status'      => 'good',
            'badge'       => array(
                'label' => __( 'Wordpress' ),
                'color' => 'blue',
            ),
            'description' => '<p></p>',
            'actions'     => '',
            'test'        => 'goo1_blog_comments',
        );

        if (get_option( "default_comment_status" ) == "open") {
            $result['status'] = 'recommended';
            $result['label'] = __( "Comments are open. SPAM risk" );
            $result["description"] = __("Your wordpress installation allows to add comments. This is a high risk to get spammed. You should disable this setting");
            $result["actions"] = '<a href="/wp-admin/options-discussion.php">Comment Settings</a>';
            return $result;
        }

        if (get_option( "default_ping_status" ) == "open") {
            $result['status'] = 'recommended';
            $result['label'] = __( "Comments Ping Status is open. SPAM risk" );
            $result["description"] = __("Your wordpress installation allows to add comments. This is a high risk to get spammed. You should disable this setting");
            $result["actions"] = '<a href="/wp-admin/options-discussion.php">Comment Settings</a>';
            return $result;
        }

        if (get_option( "default_pingback_flag" ) == 1) {
            $result['status'] = 'recommended';
            $result['label'] = __( "Comments Ping Flag is on. SPAM risk" );
            $result["description"] = __("Your wordpress installation allows to add comments. This is a high risk to get spammed. You should disable this setting");
            $result["actions"] = '<a href="/wp-admin/options-discussion.php">Comment Settings</a>';
            return $result;
        }

        return $result;
    }

    public static function test_nagios_lastused() {
        $result = array(
            'label'       => __( 'Nagios is not activated' ),
            'status'      => 'recommended',
            'badge'       => array(
                'label' => __( 'Wordpress' ),
                'color' => 'blue',
            ),
            'description' => '<p>Nagios helps you to keep the Website save.</p>',
            'actions'     => '',
            'test'        => 'goo1_nagios_lastused',
        );

        $a = get_option("goo1_omni_nagios_ts_lastused");
        if ($a == false) {
            return $result;
        }

        if ($a < time()-12*3600) {
            $result['status'] = 'recommended';
            $result['label'] = __( "Nagios Connection is not working anymore" );
            $result["description"] = __("We don't get any new request from Nagios. Last Connection: ".date("Y-m-d H:i:s T", $a));
            //$result["actions"] = '<a href="/wp-admin/options-discussion.php">Comment Settings</a>';
            return $result;
        }

        $result['status'] = 'good';
        $result['label'] = __( "Nagios check okay" );
        $result["description"] = __("Nagios is checking your website for problems. Last Connection: ".date("Y-m-d H:i:s T", $a));

        return $result;
    }

    /*
        Next Tests:
   
    */

}