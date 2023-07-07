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


}