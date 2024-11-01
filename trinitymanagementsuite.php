<?php

/*
Plugin Name: Trinity Management Suite
Plugin URI: http://deimos.hopto.org/dev/trinitymanagementsuite/
Description: TrinityCore server management plugin for Wordpress
Version: 0.2
Author: kotori
Author URI: http://deimos.hopto.org
License: GPLv2 or later
*/

/*
    Copyright 2012  Kotori  (email: kotori@deimos.hopto.org)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once( plugin_dir_path( __FILE__ ) . '/admin_page_settings.php' );
require_once( plugin_dir_path( __FILE__ ) . '/manage_registration.php' );
require_once( plugin_dir_path( __FILE__ ) . '/realm_status_widget.php' );
require_once( plugin_dir_path( __FILE__ ) . '/recaptcha/recaptchalib.php' );

/* This is a hack to get around symlink resolving issues, see
 *  http://wordpress.stackexchange.com/questions/15202/plugins-in-symlinked-directories
 *  Hopefully a better solution will be found in future versions of WordPress.
 */
if ( isset( $plugin ) )
	define( 'TRINITYMANAGEMENT_DIRECTORY', plugin_dir_url( $plugin ) );
else define( 'TRINITYMANAGEMENT_DIRECTORY', plugin_dir_url( __FILE__ ) );

/* Include the wowhead js script in our header for cool tooltips */
add_action( 'wp_head', 'trinitymanagementsuite_wowhead_script' );
function trinitymanagementsuite_wowhead_script()
{
    ?>
        <script
            type="text/javascript"
            src="http://static.wowhead.com/widgets/power.js">
        </script>
    <?php
}

/********************************************
	Begin Shortcode Registration
 ********************************************/

add_shortcode('include_playerlist', 'trinitymanagementsuite_php_include_playerlist');
function trinitymanagementsuite_php_include_playerlist($params = array())
{
	extract(shortcode_atts(array(
		'file' => 'default'
	), $params));

	ob_start();

	require_once( plugin_dir_path( __FILE__ ) . '/playerlist.php' );
	return ob_get_clean();
}

add_shortcode('include_passwordmanager', 'trinitymanagementsuite_php_include_password_manager');
function trinitymanagementsuite_php_include_password_manager($params = array())
{
        extract(shortcode_atts(array(
                'file' => 'default'
        ), $params));

        ob_start();

        require_once( plugin_dir_path( __FILE__ ) . '/pass_processor.php' );
        return ob_get_clean();
}

/********************************************
	End Shortcode Registration
 ********************************************/

/**
 * Add jQuery Validation script on posts. ?>
 */
add_action('template_redirect', 'trinitymanagementsuite_fv_scripts');
function trinitymanagementsuite_fv_scripts()
{

	wp_deregister_script('jquery');
	wp_register_script('jquery', ("http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"), false, false);
	wp_enqueue_script('jquery');
	wp_enqueue_script('validate', ("http://ajax.microsoft.com/ajax/jquery.validate/1.7/jquery.validate.min.js"), array('jquery'), false);
}


/* The password validation script is only called on the page carrying the password change script shortcode */
add_action('template_redirect', 'trinitymanagementsuite_password_validator_script');
function trinitymanagementsuite_password_validator_script()
{
        /* If the current page == our page change form, load the validation js script */
        if( is_page(get_page_by_title( 'change-password' )) )
        {
            wp_enqueue_script( 'passvalidator', plugins_url('js/pass-change-form.js',__FILE__) );
        }
}

/* This function is called when a new user is registered. */
if (!function_exists('wp_new_user_notification'))
{
	function wp_new_user_notification($user_id, $plaintext_pass = '')
	{
		$user = new WP_User($user_id);

		$user_login = stripslashes($user->user_login);
		$user_email = stripslashes($user->user_email);

		// The blogname option is escaped with esc_html on the way into the database in sanitize_option
		// we want to reverse this for the plain text arena of emails.
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

		$message  = sprintf(__('New user registration on your site %s:'), $blogname) . "\r\n\r\n";
		$message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
		$message .= sprintf(__('E-mail: %s'), $user_email) . "\r\n";

		@wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Registration'), $blogname), $message);

		if ( empty($plaintext_pass) )
		{
			return;
		}

		$message  = sprintf(__('Username: %s'), $user_login) . "\r\n";
		$message .= sprintf(__('E-mail: %s'), $user_email) . "\r\n";
		$message .= wp_login_url() . "\r\n";

		//wp_mail($user_email, sprintf(__('[%s] Your username'), $blogname), $message);

	}
}

/* Login Page Hacks */

/* Disable lost password form until we hook this function */
add_filter( 'gettext', 'remove_lostpassword_text' );
function remove_lostpassword_text ( $text )
{
    if ($text == 'Lost your password?')
    {
        $text = '';
    }
    return $text;
}

add_action( 'login_enqueue_scripts', 'trinitymanagementsuite_login_stylesheet' );
function trinitymanagementsuite_login_stylesheet()
{
    if ( in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) ) )
    {
        ?>
            <link rel="stylesheet" id="login_css"
                href="<?php echo plugins_url( 'css/login.css', __FILE__ ); ?>"
                type="text/css" media="all" />
        <?php
    }
}

add_filter( 'login_headerurl', 'trinitymanagementsuite_login_logo_url' );
function trinitymanagementsuite_login_logo_url()
{
    return get_bloginfo( 'url' );
}

add_action( 'profile_update', 'trinitymanagementsuite_profile_update' );
function trinitymanagementsuite_profile_update( $user_id )
{
    // If the profile was updated, make sure our password was as well.
    if ( ! isset( $_POST['pass1'] ) || '' == $_POST['pass1'] )
    {
        return;
    }

    /* Build the SHA version of our password for insertion into the auth database */
    $wow_password = trinitymanager_sha_password( $this_user, $_POST['pass1'] );

    /* Setup the MySQL connection with settings from the admin page */
    $user_options = get_option('trinitymanagementsuite_plugin_options');

    $wowdb = new wpdb(
        $user_options['trinitymanagementsuite_db_user_string'],
        $user_options['trinitymanagementsuite_db_pass_string'],
        $user_options['trinitymanagementsuite_db_auth_text_string'],
        $user_options['trinitymanagementsuite_db_hostname_string']
    );

    global $current_user;
    get_currentuserinfo();

    if ( false === $wowdb->query( $wowdb->prepare( "UPDATE account SET sha_pass_hash = %s WHERE username = %s", $wow_password, $current_user->user_login ) ) )
    {
        if ( $wp_error )
        {
            return new WP_Error( 'db_query_error', __( 'Could not execute query' ), $wowdb->last_error );
        }
    }

    /* Now we send out an email with our new password */
    $to = $current_user->user_email;
    $from = get_option('admin_email');

    $subject = 'Password Change Request';

    $message = 'Hello '. $current_user->user_login .',<br /><br />';
    $message .= 'Your Password was recently reset via our <a href="'. get_bloginfo( 'siteurl' ) .'">site</a>.<br />';
    $message .= 'If your did not request a new password, please <a href="mailto:' . $from .'?subject=Password Abuse Report">REPORT</a> this immediately!<br />';
    $message .= '<br />Your New Password: <b>' . $_POST['pass1'] . '</b><br />';
    $message .= '<br />';
    $message .= 'Regards, <br/>';
    $message .= '<i>Management</i><br />';

    $headers[] = "Content-Type: text/html; charset=utf-8\r\n";
    $headers[] .= 'From: '. $from . "\r\n";
    $headers[] .= 'Reply-To: ' . $from . "\r\n";
    $headers[] .= "X-Priority: 1\r\n";
    $headers[] .= 'X-Mailer: PHP';

    wp_mail( $to, $subject, $message, $headers );
}
