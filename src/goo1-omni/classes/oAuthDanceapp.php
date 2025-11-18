<?php

namespace plugins\goo1\omni;

class oAuthDanceapp {

    public static function add_login_button() {
        $auth_url = add_query_arg(array(
            'response_type' => 'code',
            'client_id' => (!empty($_SERVER["HTTP_HOST"]) ? "wordpress_omni_".$_SERVER["HTTP_HOST"] : "localhost"),
            'redirect_uri' => urlencode(wp_login_url() . '?oauth=danceapp'),
            'state' => md5(microtime(true)),
            'scope' => 'user',
        ), 'https://danceapp.net/oauth2/auth');

        // https://danceapp.net/oauth2/authorize

        // https://danceapp.net/de-de/oauth2/auth?client_id=5678.video&scope=user&state=94d1cce90994dc2c78bfc5e2bac8d329&redirect_uri=https://5678.video/de/edf%3Fact=oauth

        echo '
        
            <div style="border-top: 1px dotted #00000040; border-bottom: 1px dotted #00000040; padding: 1rem 0; margin-bottom: 0.5rem;">
                <a class="button button-primary" href="'.esc_url($auth_url).'" style="width: 100%; float: none; display: flex; align-items: center; box-shadow: #00000080 0.25rem 0.25rem 0.5rem;">
                    <img src="https://danceapp.net/favicon.png" style="height: 1rem;"/>
                    <div style="flex-grow: 1; border-left: 1px solid #fff; padding-left: 1rem; margin-left: 1rem;">Login with DanceApp</div>
                </a>
            </div>';
    }

    public static function handle_oauthdanceapp() {
        if (!isset($_GET['oauth']) || $_GET['oauth'] !== 'danceapp' || !isset($_GET['code'])) return;

        $code = sanitize_text_field($_GET['code']);

        // Token holen
        $response = file_get_contents("https://danceapp.net/api/oauth.userbycode.json?code=".urlencode($code));

        $body = json_decode($response, true);

        if (($body["result"]["user"]["id"] ?? 0) != 3) {
            wp_die('OAuth Login failed. Please contact support.');
        }


        /*if (empty($body['access_token'])) wp_die('OAuth Login fehlgeschlagen.');

        // Userinfo holen
        $user_response = wp_remote_get('https://danceapp.net/api/user', array(
            'headers' => array('Authorization' => 'Bearer ' . $body['access_token']),
        ));
        $user = json_decode(wp_remote_retrieve_body($user_response), true);
        if (empty($user['email'])) wp_die('Kein Benutzerprofil erhalten.');*/

        // WP-User suchen oder anlegen
        $wp_user = \get_user_by('id', 1);
        if (!$wp_user) {
            wp_die('User mit ID 1 nicht gefunden. Bitte Administrator kontaktieren.');
        }

        // Login
        \wp_set_auth_cookie($wp_user->ID, true);
        \wp_redirect(admin_url());
        exit;
    }

    public static function add_field_to_user_profile($user) {
        ?>
        <h3>DanceApp OAuth Info</h3>
        <table class="form-table">
            <tr>
            <th><label for="danceapp_user_id">DanceApp User ID</label></th>
            <td>
                <input type="number" name="danceapp_user_id" id="danceapp_user_id" value="<?php echo esc_attr(get_the_author_meta('danceapp_user_id', $user->ID)); ?>" class="regular-text" MIN="0" /><br />
                <span class="description">This is your DanceApp User ID linked to this WordPress account.</span>
            </td>
            </tr>
        </table>
        <?php
    }

    public static function save_field_to_user_profile($user_id) {
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }
        update_user_meta($user_id, 'danceapp_user_id', sanitize_text_field($_POST['danceapp_user_id']));
    }
}