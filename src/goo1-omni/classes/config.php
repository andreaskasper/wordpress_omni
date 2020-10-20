<?php

namespace plugins\goo1\omni;

class config {

    private static $_data = null;

    public static function get($name) {
        self::load0();
        return self::$_data[$name] ?? null;
    }

    public static function set($name, $value) {
        self::load0();
        self::$_data[$name] = $value;
        update_site_option("_goo1_omni_configs", self::$_data);
        return true;
    }


    private static function load0() {
        if (!is_null(self::$_data)) return self::$_data;
        self::$_data = get_site_option( "_goo1_omni_configs", null , true);
        if (is_null(self::$_data)) {
            add_site_option("_goo1_omni_configs", array());
            self::$_data = array();
        }
        return self::$_data;
    }

}
