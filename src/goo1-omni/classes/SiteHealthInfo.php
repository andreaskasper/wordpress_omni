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

class SiteHealthInfo {

    public static function run($debug_info) {
        $debug_info['goo1-omni-sitehealthinfo1'] = array(
            'label'    => __( 'goo1 Omni', 'my-plugin-slug' ),
            'fields'   => array(
                'license' => array(
                    'label'    => __( 'License', 'my-plugin-slug' ),
                    'value'   => "Free License",
                    'private' => false,
                ),
            ),
        );
     
        return $debug_info;
    }
}