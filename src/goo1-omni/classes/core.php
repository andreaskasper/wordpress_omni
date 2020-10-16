<?php

namespace plugins\goo1\omni;

class core {
	
  public static function init() {
    add_filter("two_factor_providers", [__CLASS__, "my_two_factor_providers"]);
    add_action('init', [__CLASS__, "action_init"]);
    add_filter("template_redirect", function() {
      if ($_SERVER["REQUEST_URI"] == "/ads.txt") \plugins\goo1\omni\adsense::adstxt();
    });
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

  public static function action_init() {
    if (is_admin()) self::action_admin_init();
    \plugins\goo1\omni\design::action_init();
    add_action( 'admin_bar_menu', [__CLASS__, "adminbar_init"], 50);
    add_role(	'owner',
		  __('Owner', "goo1-omni"),
		  array(
			  // Additional Capabilities
			  'edit_theme_options' => false,
			  // Editor Capabilities
			  'delete_others_pages' => false,
			  'delete_others_posts' => false,
			  'delete_pages' => true,
			  'delete_posts' => true,
			  'delete_private_pages' => true,
		  	'delete_private_posts' => true,
	  		'delete_published_pages' => true,
	  		'delete_published_posts' => true,
	  		'edit_others_pages' => false,
	  		'edit_others_posts' => false,
	  		'edit_pages' => true,
   			'edit_posts' => true,
	  		'edit_private_pages' => true,
	  		'edit_private_posts' => true,
	  		'edit_published_pages' => true,
  			'edit_published_posts' => true,
  			'manage_categories' => true,
	  		'manage_links' => true,
	  		'moderate_comments' => true,
	  		'publish_pages' => true,
	  		'publish_posts' => true,
	  		'read' => true,
	  		'read_private_pages' => true,
	  		'read_private_posts' => true,
	  		'unfiltered_html' => true,
	  		'upload_files' => true
		  )
    );

    wp_enqueue_style("fontawesome", "https://library.goo1.de/fontawesome/5/css/all.min.css", array(), "5", "all");
    
  }

  public static function action_admin_init() {
    add_action('admin_menu', function() {
      add_submenu_page(
        null,
        __( 'Help', 'goo1-omni' ),
        __( 'Help', 'goo1-omni' ),
        'manage_options',
        'goo1omni-reporter',
        function() {
            include(__DIR__."/../html/page_reporter.php");
      });
    });
    add_action('wp_dashboard_setup', function() {
      global $wp_meta_boxes;
      wp_add_dashboard_widget('custom_help_widget', 'goo1', function() {
          include(__DIR__."/../html/widget_dashboard.php");
      });
    });
  }

  public static function adminbar_init($admin_bar) {
    $is_personal = (get_site_option("plugin-goo1-omni-personal", 1) == 2);
    $args = array(
      'id'    => 'hdf_bugreports',
      'title' => '<span style="color: #ff0000; text-shadow: #000 0 0 1px; font-weight: bold;"><i class="far fa-life-ring" style="font-family: \'Font Awesome 5 Pro\'; font-weight: 900; margin-right: 0.5rem; font-style:normal"></i>'.($is_personal?__("ask Andi", "goo1-omni"):__("need help", "goo1-omni")).'</span>',
      "href"  => "/wp-admin/options-general.php?page=goo1omni-reporter&refurl=".urlencode("https://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"])
    );
    $admin_bar->add_node( $args );
  }
}