<?php

namespace plugins\goo1\omni;

/**
 * Configuration management class for Goo1 Omni plugin
 *
 * @package plugins\goo1\omni
 * @author goo1
 */
class config {

    private static $_data = null;

    /**
     * Retrieves a configuration value by name.
     *
     * @param string $name The name of the configuration parameter to retrieve
     * @return mixed The configuration value, or null if not found
     */
    public static function get($name) {
        self::load0();
        return self::$_data[$name] ?? null;
    }

    /**
     * Sets a configuration value by name.
     *
     * @param string $name The configuration key/name to set
     * @param mixed $value The value to assign to the configuration key
     * @return bool True on success
     */
    public static function set($name, $value) {
        self::load0();
        self::$_data[$name] = $value;
        update_site_option("_goo1_omni_configs", self::$_data);
        return true;
    }


    /**
     * Loads the initial configuration settings.
     * 
     * This private static method is responsible for loading the base configuration
     * during the initialization process. It serves as the first step in the
     * configuration loading sequence.
     *
     * @return void
     */
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
