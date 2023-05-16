<?php

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

}