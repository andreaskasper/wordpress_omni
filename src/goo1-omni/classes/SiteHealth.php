<?php

namespace plugins\goo1\omni;

class SiteHealth {

    public static function init($tests) {
        $tests["direct"]["goo1_cloudflare_active"] = array(
            "label" => __( "CDN Status" ),
            "test"  => [__CLASS__, "test_cloudflare_active"],
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

}