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

        Nagios::send($state, $status . "Free: " . $df . "/" . $ds. "; " . number_format($proz,1) . "% full");
        exit;
    }


}
