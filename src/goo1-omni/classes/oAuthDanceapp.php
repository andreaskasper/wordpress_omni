<?php

namespace plugins\goo1\omni;

class oAuthDanceapp {

    public static function add_login_button() {
        // Generate and store state for CSRF protection
        $state = wp_create_nonce('danceapp_oauth_state');
        set_transient('danceapp_oauth_state_' . $state, time(), 600); // 10 minutes
        
        $auth_url = add_query_arg(array(
            'response_type' => 'code',
            'client_id' => (!empty($_SERVER["HTTP_HOST"]) ? "wordpress_omni_".$_SERVER["HTTP_HOST"] : "localhost"),
            'redirect_uri' => urlencode(wp_login_url() . '?oauth=danceapp'),
            'state' => $state,
            'scope' => 'user',
        ), 'https://danceapp.net/oauth2/auth');

        echo '
        
            <div style="border-top: 1px dotted #00000040; border-bottom: 1px dotted #00000040; padding: 1rem 0; margin-bottom: 0.5rem;">
                <a class="button button-primary" href="'.esc_url($auth_url).'" style="width: 100%; float: none; display: flex; align-items: center; box-shadow: #00000080 0.25rem 0.25rem 0.5rem;">
                    <img src="https://danceapp.net/favicon.png" style="height: 1rem;"/>
                    <div style="flex-grow: 1; border-left: 1px solid #fff; padding-left: 1rem; margin-left: 1rem;">Login with DanceApp</div>
                </a>
            </div>';
    }

    public static function handle_oauthdanceapp() {
        if (!isset($_GET['oauth']) || $_GET['oauth'] !== 'danceapp') {
            return;
        }
        
        // Log for debugging
        error_log('DanceApp OAuth: Started handling OAuth callback');
        error_log('DanceApp OAuth: GET parameters: ' . print_r($_GET, true));
        
        // Verify state parameter (CSRF protection)
        if (!isset($_GET['state'])) {
            error_log('DanceApp OAuth Error: No state parameter received');
            wp_die(__('Authentication failed: Missing state parameter.', 'goo1-omni'));
        }
        
        $state = sanitize_text_field($_GET['state']);
        
        // Verify nonce
        if (!wp_verify_nonce($state, 'danceapp_oauth_state')) {
            error_log('DanceApp OAuth Error: Invalid state/nonce');
            wp_die(__('Authentication failed: Invalid state parameter.', 'goo1-omni'));
        }
        
        // Check if state is still valid (not expired)
        $state_time = get_transient('danceapp_oauth_state_' . $state);
        if ($state_time === false) {
            error_log('DanceApp OAuth Error: State expired or not found');
            wp_die(__('Authentication failed: State expired. Please try again.', 'goo1-omni'));
        }
        
        // Delete the used state
        delete_transient('danceapp_oauth_state_' . $state);
        
        // Check for authorization code
        if (!isset($_GET['code'])) {
            error_log('DanceApp OAuth Error: No authorization code received');
            wp_die(__('Authentication failed: No authorization code received.', 'goo1-omni'));
        }

        $code = sanitize_text_field($_GET['code']);
        error_log('DanceApp OAuth: Authorization code received: ' . substr($code, 0, 10) . '...');

        // Get user info from DanceApp API using wp_remote_get
        $api_url = 'https://danceapp.net/api/oauth.userbycode.json?code=' . urlencode($code);
        error_log('DanceApp OAuth: Calling API: ' . $api_url);
        
        $response = wp_remote_get($api_url, array(
            'timeout' => 30,
            'sslverify' => true,
        ));

        // Check for errors
        if (is_wp_error($response)) {
            error_log('DanceApp OAuth Error: API request failed: ' . $response->get_error_message());
            wp_die(__('Authentication failed: Could not connect to DanceApp API.', 'goo1-omni') . ' ' . $response->get_error_message());
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            error_log('DanceApp OAuth Error: API returned status code ' . $response_code);
            wp_die(__('Authentication failed: DanceApp API returned error code ', 'goo1-omni') . $response_code);
        }

        $body = wp_remote_retrieve_body($response);
        error_log('DanceApp OAuth: API Response: ' . $body);
        
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('DanceApp OAuth Error: Failed to parse JSON response: ' . json_last_error_msg());
            wp_die(__('Authentication failed: Invalid response from DanceApp API.', 'goo1-omni'));
        }

        // Extract user ID from response
        $danceapp_user_id = $data['result']['user']['id'] ?? 0;
        
        if (empty($danceapp_user_id)) {
            error_log('DanceApp OAuth Error: No user ID in response');
            wp_die(__('Authentication failed: No user information received from DanceApp.', 'goo1-omni'));
        }
        
        error_log('DanceApp OAuth: DanceApp User ID: ' . $danceapp_user_id);
        
        // Check if DanceApp User ID is 3
        if ($danceapp_user_id != 3) {
            error_log('DanceApp OAuth Error: DanceApp User ID ' . $danceapp_user_id . ' is not authorized (only ID 3 allowed)');
            wp_die(__('Authentication failed: You are not authorized to login. Please contact support.', 'goo1-omni'));
        }

        // Get WordPress user with ID 1
        $wp_user = get_user_by('id', 1);

        if (!$wp_user) {
            error_log('DanceApp OAuth Error: WordPress user with ID 1 not found');
            wp_die(__('Authentication failed: WordPress user not found. Please contact an administrator.', 'goo1-omni'));
        }
        error_log('DanceApp OAuth: Found WordPress user: ' . $wp_user->user_login . ' (ID: ' . $wp_user->ID . ')');

        // Login the user
        wp_set_auth_cookie($wp_user->ID, true);
        error_log('DanceApp OAuth: User logged in successfully');
        
        // Redirect to admin
        wp_redirect(admin_url());
        exit;
    }

    public static function add_field_to_user_profile($user) {
        ?>
        <h3><?php _e('DanceApp OAuth Info', 'goo1-omni'); ?></h3>
        <table class="form-table">
            <tr>
            <th><label for="danceapp_user_id"><?php _e('DanceApp User ID', 'goo1-omni'); ?></label></th>
            <td>
                <input type="number" name="danceapp_user_id" id="danceapp_user_id" value="<?php echo esc_attr(get_the_author_meta('danceapp_user_id', $user->ID)); ?>" class="regular-text" min="0" /><br />
                <span class="description"><?php _e('This is your DanceApp User ID linked to this WordPress account. Leave empty if not using DanceApp login.', 'goo1-omni'); ?></span>
            </td>
            </tr>
        </table>
        <?php
    }

    public static function save_field_to_user_profile($user_id) {
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }
        
        // Verify nonce if this is being saved from a form
        if (isset($_POST['danceapp_user_id'])) {
            $danceapp_id = sanitize_text_field($_POST['danceapp_user_id']);
            
            // Only update if value is provided
            if ($danceapp_id !== '') {
                update_user_meta($user_id, 'danceapp_user_id', intval($danceapp_id));
            } else {
                delete_user_meta($user_id, 'danceapp_user_id');
            }
        }
    }
}