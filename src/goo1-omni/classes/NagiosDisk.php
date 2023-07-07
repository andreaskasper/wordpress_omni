<?php

namespace plugins\goo1\omni;

class NagiosDisk {

    public static function test() {
        global $wp_version;

        require_once(__DIR__."/Nagios.php");

        $ds = disk_total_space(__DIR__);
        $df = disk_free_space(__DIR__);

        $proz = 100-(100*$df/$ds);

        $state = 0;
        $status = "";

        if ($df/$ds <= 0.05) {
            $status = 'CRITICAL - ';
            $state = 2;
        } elseif ($df/$ds <= 0.2) {
            $status = 'WARNING - ';
            $state = 1;
        }

        header("Cache-Control: public, max-age=60, s-maxage=60");
        Nagios::send($state, $status . "Free: " . self::format_bytes($df,1) . "/" . self::format_bytes($ds,1). "; " . number_format($proz,1) . "% full");
        exit;
    }

    private static function format_bytes($bytes, $precision = 2) { 
        $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
    
        $bytes = max($bytes, 0); 
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
        $pow = min($pow, count($units) - 1); 
    
        // Uncomment one of the following alternatives
        // $bytes /= pow(1024, $pow);
        $bytes /= (1 << (10 * $pow)); 
    
        return round($bytes, $precision) . ' ' . $units[$pow]; 
    }  


}
