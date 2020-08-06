<?php

namespace plugins\goo1\omni;

class core {
	
	public static function init() {
		add_action( 'rest_api_init', function () {
            register_rest_route( 'nagios', '/versions.json', array(
              'methods' => 'GET',
              'callback' => ["\plugins\goo1\omni\NagiosVersions", "test"],
            ) );
          } );
        add_action('admin_menu', function() {
            add_submenu_page('options-general.php',"goo1 Omni", "goo1 Omni", "manage_options", "goo1omni-settings", function() {
                echo("<h1>goo1 Omni Settings</h1>");
                echo('<hr class="wp-header-end">');
                echo("Diese Seite muss noch programmiert werden");
            });
          });
		
	}

}