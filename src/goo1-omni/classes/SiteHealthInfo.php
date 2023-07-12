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
        $ds = disk_total_space(__DIR__);
        $df = disk_free_space(__DIR__);
        $debug_info['goo1-omni-sitehealthinfo1'] = array(
            'label'    => __( 'goo1 Omni', 'my-plugin-slug' ),
            'fields'   => array(
                'license' => array(
                    'label'    => __( 'License', 'my-plugin-slug' ),
                    'value'   => "Free License",
                    'private' => false
                ),
                'disksize' => array(
                    'label'    => __( 'Disk Size', 'my-plugin-slug' ),
                    'value'   => self::format_bytes($ds, 1),
                    'private' => false
                ),
                'diskfree' => array(
                    'label'    => __( 'Disk Free', 'my-plugin-slug' ),
                    'value'   => self::format_bytes($df, 1),
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

    public static function format_bytes($bytes, $precision = 2) { 
        $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
    
        $bytes = max($bytes, 0); 
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
        $pow = min($pow, count($units) - 1); 
    
        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);
        // $bytes /= (1 << (10 * $pow)); 
    
        return round($bytes, $precision) . ' ' . $units[$pow]; 
    }

}