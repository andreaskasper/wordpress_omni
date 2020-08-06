<?php

namespace plugins\goo1\omni;

class Nagios {

    public static function send(int $state, string $txt, $value = null) {
        $out = array();
        $out["result"]["state"] = $state;
        $out["result"]["txt"] = $txt;
        if (!is_null($value)) {
            $out["result"]["perf"]["value"] = $value;
        }
        $out["err"]["id"] = 0;
        $out["err"]["msg"] = "";
        $out["runtime"]["begin"] = date("c");
        header("Content-Type: application/json; charset=utf-8");
        die(json_encode($out));
    }


}