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
                    'private' => false
                ),
                'nagioslastused' => array(
                    'label'    => __( 'Nagios Last Used', 'my-plugin-slug' ),
                    'value'   => date("Y-m-d H:i:s T", get_option("goo1_omni_nagios_ts_lastused", 0)),
                    'private' => false
                ),
            ),
        );
     
        return $debug_info;
    }
}