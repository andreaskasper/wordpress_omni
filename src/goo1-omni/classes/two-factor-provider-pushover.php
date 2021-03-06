<?php
/**
 * Class for creating an email provider.
 *
 * @since 0.1-dev
 *
 * @package Two_Factor
 */

class Two_Factor_Pushover extends \Two_Factor_Provider {

	/**
	 * The user meta token key.
	 *
	 * @var string
	 */
	const TOKEN_META_KEY = '_two_factor_pushover_token';

	/**
	 * Store the timestamp when the token was generated.
	 *
	 * @var string
	 */
	const TOKEN_META_KEY_TIMESTAMP = '_two_factor_pushover_token_timestamp';

	/**
	 * Name of the input field used for code resend.
	 *
	 * @var string
	 */
	const INPUT_NAME_RESEND_CODE = 'two-factor-pushover-code-resend';

	/**
	 * Ensures only one instance of this class exists in memory at any one time.
	 *
	 * @since 0.1-dev
	 */
	static function get_instance() {
		static $instance;
		$class = __CLASS__;
		if ( ! is_a( $instance, $class ) ) {
			$instance = new $class;
		}
		return $instance;
	}

	/**
	 * Class constructor.
	 *
	 * @since 0.1-dev
	 */
	protected function __construct() {
		add_action( 'two-factor-user-options-' . __CLASS__, array( $this, 'user_options' ) );
		add_action( 'personal_options_update', array( $this, 'user_two_factor_options_update' ) );
		add_action( 'edit_user_profile_update', array( $this, 'user_two_factor_options_update' ) );
		return parent::__construct();
	}

	/**
	 * Returns the name of the provider.
	 *
	 * @since 0.1-dev
	 */
	public function get_label() {
		return _x( 'Pushover', 'Provider Label', 'two-factor' );
	}

	/**
	 * Generate the user token.
	 *
	 * @since 0.1-dev
	 *
	 * @param int $user_id User ID.
	 * @return string
	 */
	public function generate_token( $user_id ) {
		$token = $this->get_code();
		$token = substr($token, 0, 6);

		update_user_meta( $user_id, self::TOKEN_META_KEY_TIMESTAMP, time() );
		update_user_meta( $user_id, self::TOKEN_META_KEY, wp_hash( $token ) );

		return $token;
	}

	/**
	 * Check if user has a valid token already.
	 *
	 * @param  int $user_id User ID.
	 * @return boolean      If user has a valid email token.
	 */
	public function user_has_token( $user_id ) {
		$hashed_token = $this->get_user_token( $user_id );

		if ( ! empty( $hashed_token ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Has the user token validity timestamp expired.
	 *
	 * @param integer $user_id User ID.
	 *
	 * @return boolean
	 */
	public function user_token_has_expired( $user_id ) {
		$token_lifetime = $this->user_token_lifetime( $user_id );
		$token_ttl = $this->user_token_ttl( $user_id );

		// Invalid token lifetime is considered an expired token.
		if ( is_int( $token_lifetime ) && $token_lifetime <= $token_ttl ) {
			return false;
		}

		return true;
	}

	/**
	 * Get the lifetime of a user token in seconds.
	 *
	 * @param integer $user_id User ID.
	 *
	 * @return integer|null Return `null` if the lifetime can't be measured.
	 */
	public function user_token_lifetime( $user_id ) {
		$timestamp = intval( get_user_meta( $user_id, self::TOKEN_META_KEY_TIMESTAMP, true ) );

		if ( ! empty( $timestamp ) ) {
			return time() - $timestamp;
		}

		return null;
	}

	/**
	 * Return the token time-to-live for a user.
	 *
	 * @param integer $user_id User ID.
	 *
	 * @return integer
	 */
	public function user_token_ttl( $user_id ) {
		$token_ttl = 15 * MINUTE_IN_SECONDS;

		/**
		 * Number of seconds the token is considered valid
		 * after the generation.
		 *
		 * @param integer $token_ttl Token time-to-live in seconds.
		 * @param integer $user_id User ID.
		 */
		return (int) apply_filters( 'two_factor_token_ttl', $token_ttl, $user_id );
	}

	/**
	 * Get the authentication token for the user.
	 *
	 * @param  int $user_id    User ID.
	 *
	 * @return string|boolean  User token or `false` if no token found.
	 */
	public function get_user_token( $user_id ) {
		$hashed_token = get_user_meta( $user_id, self::TOKEN_META_KEY, true );

		if ( ! empty( $hashed_token ) && is_string( $hashed_token ) ) {
			return $hashed_token;
		}

		return false;
	}

	/**
	 * Validate the user token.
	 *
	 * @since 0.1-dev
	 *
	 * @param int    $user_id User ID.
	 * @param string $token User token.
	 * @return boolean
	 */
	public function validate_token( $user_id, $token ) {
		$hashed_token = $this->get_user_token( $user_id );

		// Bail if token is empty or it doesn't match.
		if ( empty( $hashed_token ) || ( wp_hash( $token ) !== $hashed_token ) ) {
			return false;
		}

		if ( $this->user_token_has_expired( $user_id ) ) {
			return false;
		}

		// Ensure the token can be used only once.
		$this->delete_token( $user_id );

		return true;
	}

	/**
	 * Delete the user token.
	 *
	 * @since 0.1-dev
	 *
	 * @param int $user_id User ID.
	 */
	public function delete_token( $user_id ) {
		delete_user_meta( $user_id, self::TOKEN_META_KEY );
	}

	/**
	 * Generate and email the user token.
	 *
	 * @since 0.1-dev
	 *
	 * @param WP_User $user WP_User object of the logged-in user.
	 * @return bool Whether the email contents were sent successfully.
	 */
	public function generate_and_email_token( $user ) {

		$token = $this->generate_token( $user->ID );
		$token2 = substr($token, 0, 3)." ".substr($token, 3, 3);

		/* translators: %s: site name */
		$subject = wp_strip_all_tags( sprintf( __( 'Your login confirmation code for %s', 'two-factor' ), get_bloginfo( 'name' ) ) );
		/* translators: %s: token */
        $message = wp_strip_all_tags( sprintf( __( 'Enter %s to log in.', 'two-factor' ), $token ) );
        
        switch ($user->user_email) {
            default:
                $pushover_user_id = "uihmdiaj3cgnvrn24mpfr58xotwpjy"; break;
		}
		
		$pushover_user_id = get_user_meta($user->id, "two-factor-pushover-apikey",true) ?? "?";

		echo($user->id.PHP_EOL);
		echo($pushover_user_id);

        $po = new \plugins\goo1\omni\Pushover();
        $po->setToken("ay32dvhh22vyzvgpefxga8jaqd2zs6");
        $po->setUser($pushover_user_id);
        $po->setTitle($token2);
        $po->setMessage("Dein PIN für ".$_SERVER["HTTP_HOST"]." ist ".$token.".".PHP_EOL."Der Code wurde generiert am ".date("d.m.Y")." um ".date("H:i:s")."Uhr.");
        $po->setUrl("https://".$_SERVER["HTTP_HOST"]."/wp-login.php");
        $po->setUrlTitle("Login");
        $po->setSound("cosmic");
        $po->setExpire(900);
        $po->send();


		/**
		 * Filter the token email subject.
		 *
		 * @param string $subject The email subject line.
		 * @param int    $user_id The ID of the user.
		 */
		//$subject = apply_filters( 'two_factor_token_email_subject', $subject, $user->ID );

		/**
		 * Filter the token email message.
		 *
		 * @param string $message The email message.
		 * @param string $token   The token.
		 * @param int    $user_id The ID of the user.
		 */
		//$message = apply_filters( 'two_factor_token_email_message', $message, $token, $user->ID );

		return true; //wp_mail( $user->user_email, $subject, $message );
	}

	/**
	 * Prints the form that prompts the user to authenticate.
	 *
	 * @since 0.1-dev
	 *
	 * @param WP_User $user WP_User object of the logged-in user.
	 */
	public function authentication_page( $user ) {
		if ( ! $user ) {
			return;
		}

		if ( ! $this->user_has_token( $user->ID ) || $this->user_token_has_expired( $user->ID ) ) {
			$this->generate_and_email_token( $user );
		}

		require_once( ABSPATH .  '/wp-admin/includes/template.php' );
		?>
		<p><?php esc_html_e( 'Ein PIN wurde an Dein Pushover gesendet.', 'two-factor' ); ?></p>
		<p>
			<label for="authcode"><?php esc_html_e( 'Verification Code:', 'two-factor' ); ?></label>
			<input type="tel" name="two-factor-pushover-code" id="authcode" class="input" value="" size="20" pattern="[0-9]*" />
			<?php submit_button( __( 'Log In', 'two-factor' ) ); ?>
		</p>
		<p class="two-factor-email-resend">
			<input type="submit" class="button" name="<?php echo esc_attr( self::INPUT_NAME_RESEND_CODE ); ?>" value="<?php esc_attr_e( 'Resend Code', 'two-factor' ); ?>" />
		</p>
		<script type="text/javascript">
			setTimeout( function(){
				var d;
				try{
					d = document.getElementById('authcode');
					d.value = '';
					d.focus();
				} catch(e){}
			}, 200);
		</script>
		<?php
	}

	/**
	 * Send the email code if missing or requested. Stop the authentication
	 * validation if a new token has been generated and sent.
	 *
	 * @param  WP_USer $user WP_User object of the logged-in user.
	 * @return boolean
	 */
	public function pre_process_authentication( $user ) {
		if ( isset( $user->ID ) && isset( $_REQUEST[ self::INPUT_NAME_RESEND_CODE ] ) ) {
			$this->generate_and_email_token( $user );
			return true;
		}

		return false;
	}

	/**
	 * Validates the users input token.
	 *
	 * @since 0.1-dev
	 *
	 * @param WP_User $user WP_User object of the logged-in user.
	 * @return boolean
	 */
	public function validate_authentication( $user ) {
		if ( ! isset( $user->ID ) || ! isset( $_REQUEST['two-factor-pushover-code'] ) ) {
			return false;
		}

		return $this->validate_token( $user->ID, $_REQUEST['two-factor-pushover-code'] );
	}

	/**
	 * Whether this Two Factor provider is configured and available for the user specified.
	 *
	 * @since 0.1-dev
	 *
	 * @param WP_User $user WP_User object of the logged-in user.
	 * @return boolean
	 */
	public function is_available_for_user( $user ) {
		return true;
	}

	/**
	 * Inserts markup at the end of the user profile field for this provider.
	 *
	 * @since 0.1-dev
	 *
	 * @param WP_User $user WP_User object of the logged-in user.
	 */
	public function user_options( $user ) {
		$email = $user->user_email;
		$key = get_user_meta($user->id, "two-factor-pushover-apikey",true) ?? "?";
		?>
		<div>

			<input type="hidden" name="two-factor-totp-key" value="<?php echo esc_attr( $key ); ?>" />
				<label for="two-factor-totp-authcode">
					<?php esc_html_e( 'Pushover User Key:', 'two-factor' ); ?>
					<input type="text" name="two-factor-pushover-key" id="two-factor-totp-authcode" class="input" value="<?=$key ?>" size="20" pattern="[A-Za-z0-9]*" />
				</label>
				<input type="submit" class="button" name="two-factor-pushover-submit" value="<?php esc_attr_e( 'Submit', 'two-factor' ); ?>" />
			<?php
			/*echo("abc");
			echo esc_html( sprintf(
				/* translators: %s: email address * /
				__( 'Der PIN wird über die App Pushover gesendet.', 'two-factor' ),
				$email
			) );*/
			?>
		</div>
		<?php
	}

	public function user_two_factor_options_update( $user_id ) {
		if (!empty($_POST["two-factor-pushover-key"])) {
			$key = sanitize_text_field($_POST["two-factor-pushover-key"]);
			update_user_meta( $user_id, "two-factor-pushover-apikey", $key );
		}
	}
}
