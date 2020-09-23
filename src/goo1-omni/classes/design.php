<?php

namespace plugins\goo1\omni;

class design {




    public static function action_init() {
        if (get_site_option("plugin-goo1-show_login", false) == true) add_action( 'login_head', [__CLASS__, "login_webseite"]);

        if (get_site_option("plugin-goo1-show_logo", false) == true) {
            add_action('admin_menu', function() {
                global $menu;
                $url = '#';
                $menu[0] = array( __('goo1'), 'read', $url, 'goo1-logo', 'goo1-logo');
            });
            add_action('admin_head', function() {
                echo('<style>
                #adminmenu a.goo1-logo { display: block; background: url(/wp-content/plugins/goo1-omni/assets/images/logo_goo1_white.png) no-repeat center center; background-size: contain; width: 140px; opacity: 1; height: 40px; margin: 0 auto 0.5rem auto; padding: 10px 5px; transition: all 300ms ease; }
                #adminmenu a.goo1-logo:hover { opacity: 0.8; }
                #adminmenu a.goo1-logo div.wp-menu-name { display: none; }
                </style>');
            });
        }

        add_action('admin_bar_menu', function($wp_admin_bar) {
            $wp_admin_bar->remove_node('wp-logo');
            $wp_admin_bar->remove_node('updates');
            if (get_site_option("plugin-goo1-use_comments", true) == false) $wp_admin_bar->remove_node('comments');
        }, 999);
        add_action( 'admin_menu', function() {
            $user = wp_get_current_user();
            $roles = ( array ) $user->roles;
            if (in_array("owner",$roles)) {
                remove_menu_page( 'themes.php' );
                remove_menu_page( 'options-general.php');
                remove_menu_page( 'edit.php?post_type=elementor_library');
                remove_menu_page( 'tools.php');
                if (get_site_option("plugin-goo1-use_comments", true) == false) remove_menu_page( 'edit-comments.php');
            }
            //print_r($_GLOBALS);
         }, 999 );
    }

    public static function login_webseite() {
        echo('<style>
        body { background: url(https://i.imgur.com/83wTpw5.jpg) no-repeat center center; background-size: cover;  }
        /*#login h1 > a { width: 100%; background-image: url(/wp-content/plugins/goo1hdfutils/assets/images/logo_dokpress.png); background-size: contain !important; opacity: 0.9; transition: all 300ms ease; }*/
        #login h1 > a:hover { opacity: 1; }
        #login h1 { display: none; } 
        body.login-action-login.wp-core-ui #nav a, 
        body.login-action-login.wp-core-ui #backtoblog a,
        body.login-action-login.wp-core-ui .privacy-policy-page-link a,
        body.login-action-login.wp-core-ui .backup-methods-wrap a { color: #ffffff80; transition: all 300ms ease; }

        body.login-action-login.wp-core-ui #nav a:hover,
        body.login-action-login.wp-core-ui #backtoblog a:hover,
        body.login-action-login.wp-core-ui .privacy-policy-page-link a:hover,
        body.login-action-login.wp-core-ui .backup-methods-wrap a:hover { color: #ffffff; }

        #login form { background: #ffffffc0; border-radius: 1rem; box-shadow: #00000080 1rem 1rem 1rem; }
        
        </style>');
    }

}

