<?php
/**
 * Plugin Name: goo1 Omni
 * Plugin URI: https://github.com/andreaskasper/
 * Description: Important functions for goo1 websites
 * Author: Andreas Kasper
 * Version: 0.1.43
 * Author URI: https://github.com/andreaskasper/
 * Network: True
 * Text Domain: goo1-omni
 */

spl_autoload_register(function ($class_name) {
	if (substr($class_name,0,18) != "plugins\\goo1\\omni\\") return false;
	$files = array(
		__DIR__."/classes/".str_replace("\\", DIRECTORY_SEPARATOR,substr($class_name, 18)).".php"
	);
	foreach ($files as $file) {
		if (file_exists($file)) {
			include($file);
			return true;
		}
	}
	return false;
});

add_action( 'plugins_loaded', function() {
    load_plugin_textdomain( 'goo1-omni', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
});
\plugins\goo1\omni\core::init();

if (!class_exists("Puc_v4_Factory")) {
	require_once(__DIR__."/plugin-update-checker/plugin-update-checker.php");
}
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    "https://raw.githubusercontent.com/andreaskasper/wordpress_omni/master/dist/updater.json",
    __FILE__, //Full path to the main plugin file or functions.php.
    'goo1-omni'
);

