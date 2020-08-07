<?php

namespace plugins\goo1\omni;

class core {
	
  public static function init() {
    add_filter("two_factor_providers", [__CLASS__, "my_two_factor_providers"]);
		add_action( 'rest_api_init', function () {
      register_rest_route( 'nagios', '/versions.json', array(
        'methods' => 'GET',
        'callback' => ["\plugins\goo1\omni\NagiosVersions", "test"],
      ));
    });
    add_action('admin_menu', function() {
      add_submenu_page('options-general.php',"goo1 Omni", "goo1 Omni", "manage_options", "goo1omni-settings", function() {
        include(__DIR__."/../html/page_settings.php");
      });
    });
    if (!empty($_SERVER["HTTP_CF_RAY"])) {
      $a = new CloudflareFlexibleSSL();
      $a->run();
    }
  }
  
  public static function my_two_factor_providers($methods) {
    $methods['Two_Factor_Pushover'] = __DIR__.'/two-factor-provider-pushover.php';
    return $methods;
  }
}