<?php

/*  Copyright 2012  Kotori  (email : kotori@deimos.hopto.org)

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

/* Hooks/filters */
register_activation_hook(__FILE__, 'trinitymanagementsuite_add_defaults');
register_deactivation_hook( __FILE__, 'trinitymanagementsuite_remove' );

add_action('admin_menu', 'trinitymanagementsuite_admin_add_page');
add_action('admin_init', 'trinitymanagementsuite_admin_init');

/* Hook the admin options page */
function trinitymanagementsuite_admin_add_page()
{
	add_options_page(
		'Trinity Management Suite Plugin Page',		// Page title. (required)
		'Trinity Management Suite Plugin Menu',		// Menu title. (required)
		'manage_options',				// Capability. (required)
		__FILE__,					// Menu slug. (required)
		'trinitymanagementsuite_options_page'		// Callback function. (optional)
	);
}

/* Display the admin options page */
function trinitymanagementsuite_options_page()
{
	?>
	<div class ="wrap">
		<div class="icon32" id="icon-plugins"><br></div>
		<h2>Trinity Management Suite Plugin Settings</h2>

		<?php settings_errors(); ?>

		<form action="options.php" method="post">
			<?php settings_fields('trinitymanagementsuite_plugin_options'); ?>
			<?php do_settings_sections(__FILE__); ?>

			<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" /><p>
		</form>
	</div>
	<?php
	test_database_connectivity();
}

function test_database_connectivity()
{
	/* Test the DB connection here in the settings page so we can see quickly if the settings work */

	/**
         * World DB
	 */
        echo '<p>World Database Connection Status: ';
        $db_test_options = get_option('trinitymanagementsuite_plugin_options');
        $mysqli_conn = new mysqli(
                $db_test_options['trinitymanagementsuite_db_hostname_string'],  // Hostname
                $db_test_options['trinitymanagementsuite_db_user_string'],      // Username
                $db_test_options['trinitymanagementsuite_db_pass_string'],      // Password
                $db_test_options['trinitymanagementsuite_db_world_text_string'] // Database
        );
        if($mysqli_conn->connect_errno)
        {
                echo "<font color='red'><b>Failed!</b></font><p>" . $mysqli_conn->connect_error . "</p>";
        }
        else
        {
                echo "<font color='green'><b>Success!</b></font><p>";
        }

	/* reset all and select a new database */
	$mysqli_conn = new mysqli(
                $db_test_options['trinitymanagementsuite_db_hostname_string'],  // Hostname
                $db_test_options['trinitymanagementsuite_db_user_string'],      // Username
                $db_test_options['trinitymanagementsuite_db_pass_string'],      // Password
                $db_test_options['trinitymanagementsuite_db_char_text_string']	// Database
        );

	/**
         * Char DB
         */
        echo '</p>';
        echo '<p>Char Database Connection Status: ';
        if($mysqli_conn->connect_errno)
        {
                echo "<font color='red'><b>Failed!</b></font><p>" . $mysqli_conn->connect_error . "</p>";
        }
        else
        {
                echo "<font color='green'><b>Success!</b></font><p>";
        }

	$mysqli_conn = new mysqli(
                $db_test_options['trinitymanagementsuite_db_hostname_string'],  // Hostname
                $db_test_options['trinitymanagementsuite_db_user_string'],      // Username
                $db_test_options['trinitymanagementsuite_db_pass_string'],      // Password
                $db_test_options['trinitymanagementsuite_db_auth_text_string']  // Database
        );

        /**
         * Auth DB
         */
	echo '</p>';
        echo '<p>Auth Database Connection Status: ';
        if($mysqli_conn->connect_errno)
        {
                echo "<font color='red'><b>Failed!</b></font><p>" . $mysqli_conn->connect_error . "</p>";
        }
        else
        {
                echo "<font color='green'><b>Success!</b></font><p>";
        }
        $mysqli_conn->close();

        /* END DB TEST */
}

/* Add the admin settings */
function trinitymanagementsuite_admin_init()
{
	register_setting(
		'trinitymanagementsuite_plugin_options',		// Settings page
		'trinitymanagementsuite_plugin_options',		// Option name
		'trinitymanagementsuite_plugin_options_validate'	// Validation callback
	);

	add_settings_section(
		'plugin_main',			// Id
		'Main Settings',		// Title
		'plugin_section_text',		// Callback function
		 __FILE__			// Page
	);

	/*  <?php add_settings_field( $id, $title, $callback, $page, $section, $args ); ?> */
	add_settings_field('trinitymanagementsuite_recaptcha_apikey_pub_string', 'Recaptcha Public API Key', 'recaptcha_apikey_pub_plugin_setting_string', __FILE__, 'plugin_main');
	add_settings_field('trinitymanagementsuite_recaptcha_apikey_priv_string', 'Recaptcha Private API Key', 'recaptcha_apikey_priv_plugin_setting_string', __FILE__, 'plugin_main');

	add_settings_field('trinitymanagementsuite_db_hostname_string', 'WoW Database Hostname', 'db_hostname_plugin_setting_string', __FILE__, 'plugin_main');

	add_settings_field('trinitymanagementsuite_db_user_string', 'WoW Database Username', 'db_user_plugin_setting_string', __FILE__, 'plugin_main');
	add_settings_field('trinitymanagementsuite_db_pass_string', 'WoW Database Password', 'db_pass_plugin_setting_string', __FILE__, 'plugin_main');

	add_settings_field('trinitymanagementsuite_db_world_text_string', 'World DB Name (world)', 'db_world_plugin_setting_string', __FILE__, 'plugin_main');
	add_settings_field('trinitymanagementsuite_db_char_text_string', 'Char DB Name (characters)', 'db_char_plugin_setting_string', __FILE__, 'plugin_main');
	add_settings_field('trinitymanagementsuite_db_auth_text_string', 'Auth DB Name (auth)', 'db_auth_plugin_setting_string', __FILE__, 'plugin_main');

	add_settings_field('trinitymanagementsuite_world_port_string', 'World Port (8085)', 'world_port_plugin_setting_string', __FILE__, 'plugin_main');
	add_settings_field('trinitymanagementsuite_auth_port_string', 'Authentication Port (3724)', 'auth_port_plugin_setting_string', __FILE__, 'plugin_main');
}

function plugin_section_text()
{
	echo '<p>Take control over your TrinityCore server by adjusting the values below</p>';
}


/*
 * Callback: recaptcha_apikey_pub_plugin_setting_string()
 * Handles: trinitymanagementsuite_recaptcha_apikey_pub_string
 */
function recaptcha_apikey_pub_plugin_setting_string()
{
        $options = get_option('trinitymanagementsuite_plugin_options');
?>
	<input id='trinitymanagementsuite_recaptcha_apikey_pub_string' name='trinitymanagementsuite_plugin_options[trinitymanagementsuite_recaptcha_apikey_pub_string]' size='32' type='text' value="<?php esc_attr_e($options['trinitymanagementsuite_recaptcha_apikey_pub_string'] ); ?>" />
<?php
}

/*
 * Callback: recaptcha_apikey_priv_plugin_setting_string()
 * Handles: trinitymanagementsuite_recaptcha_apikey_priv_string
 */
function recaptcha_apikey_priv_plugin_setting_string()
{
        $options = get_option('trinitymanagementsuite_plugin_options');
?>
	<input id='trinitymanagementsuite_recaptcha_apikey_priv_string' name='trinitymanagementsuite_plugin_options[trinitymanagementsuite_recaptcha_apikey_priv_string]' size='32' type='text' value="<?php esc_attr_e($options['trinitymanagementsuite_recaptcha_apikey_priv_string'] ); ?>" />
<?php
}


/*
 * Callback: db_user_plugin_setting_string()
 * Handles: trinitymanagementsuite_db_user_text_string
 */
function db_user_plugin_setting_string()
{
        $options = get_option('trinitymanagementsuite_plugin_options');
?>
	<input id='trinitymanagementsuite_db_user_string' name='trinitymanagementsuite_plugin_options[trinitymanagementsuite_db_user_string]' size='32' type='text' value="<?php esc_attr_e($options['trinitymanagementsuite_db_user_string'] ); ?>" />
<?php
}

/*
 * Callback: db_hostname_plugin_setting_string()
 * Handles: trinitymanagementsuite_db_hostname_string
 */
function db_hostname_plugin_setting_string()
{
	$options = get_option('trinitymanagementsuite_plugin_options');
?>
	<input id='trinitymanagementsuite_db_hostname_string' name='trinitymanagementsuite_plugin_options[trinitymanagementsuite_db_hostname_string]' size='32' type='text' value="<?php esc_attr_e($options['trinitymanagementsuite_db_hostname_string'] ); ?>" />
<?php
}

/*
 * Callback: db_pass_plugin_setting_string()
 * Handles: trinitymanagementsuite_db_pass_text_string
 */
function db_pass_plugin_setting_string()
{
        $options = get_option('trinitymanagementsuite_plugin_options');
?>
	<input id='trinitymanagementsuite_db_pass_string' type="password" name='trinitymanagementsuite_plugin_options[trinitymanagementsuite_db_pass_string]' size='32' type='text' value="<?php esc_attr_e($options['trinitymanagementsuite_db_pass_string'] ); ?>" />
<?php
}


/*
 * Callback: db_world_plugin_setting_string()
 * Handles: trinitymanagementsuite_db_world_text_string
 */
function db_world_plugin_setting_string()
{
	$options = get_option('trinitymanagementsuite_plugin_options');
?>
	<input id='trinitymanagementsuite_db_world_text_string' name='trinitymanagementsuite_plugin_options[trinitymanagementsuite_db_world_text_string]' size='32' type='text' value="<?php esc_attr_e($options['trinitymanagementsuite_db_world_text_string'] ); ?>" />
<?php

}


/*
 * Callback: db_char_plugin_setting_string()
 * Handles: trinitymanagementsuite_db_char_text_string
 */
function db_char_plugin_setting_string()
{
        $options = get_option('trinitymanagementsuite_plugin_options');
?>
        <input id='trinitymanagementsuite_char_world_text_string' name='trinitymanagementsuite_plugin_options[trinitymanagementsuite_db_char_text_string]' size='32' type='text' value="<?php esc_attr_e($options['trinitymanagementsuite_db_char_text_string'] ); ?>" />
<?php
}


/*
 * Callback: db_auth_plugin_setting_string()
 * Handles: trinitymanagementsuite_db_auth_text_string
 */
function db_auth_plugin_setting_string()
{
        $options = get_option('trinitymanagementsuite_plugin_options');
?>
	<input id='trinitymanagementsuite_db_auth_text_string' name='trinitymanagementsuite_plugin_options[trinitymanagementsuite_db_auth_text_string]' size='32' type='text' value="<?php esc_attr_e($options['trinitymanagementsuite_db_auth_text_string'] ); ?>" />
<?php
}


/*
 * Callback: world_port_plugin_setting_string()
 * Handles: trinitymanagementsuite_world_port_string
 */
function world_port_plugin_setting_string()
{
	$options = get_option('trinitymanagementsuite_plugin_options');
?>
	<input id='trinitymanagementsuite_world_port_string' name='trinitymanagementsuite_plugin_options[trinitymanagementsuite_world_port_string]' size='32' type='text' value="<?php esc_attr_e($options['trinitymanagementsuite_world_port_string'] ); ?>" />
<?php
}


/*
 * Callback: auth_port_plugin_setting_string()
 * Handles: trinitymanagementsuite_auth_port_string
 */
function auth_port_plugin_setting_string()
{
	$options = get_option('trinitymanagementsuite_plugin_options');
?>
	<input id='trinitymanagementsuite_auth_port_string' name='trinitymanagementsuite_plugin_options[trinitymanagementsuite_auth_port_string]' size='32' type='text' value="<?php esc_attr_e($options['trinitymanagementsuite_auth_port_string'] ); ?>" />
<?php
}


/* Options validation function */
function trinitymanagementsuite_plugin_options_validate($input)
{
	// Check our textbox option fields contain no HTML tags - if so strip them out
	
	$input['trinitymanagementsuite_recaptcha_apikey_pub_string'] = wp_filter_nohtml_kses($input['trinitymanagementsuite_recaptcha_apikey_pub_string']);
	$input['trinitymanagementsuite_recaptcha_apikey_priv_string'] = wp_filter_nohtml_kses($input['trinitymanagementsuite_recaptcha_apikey_priv_string']);

	$input['trinitymanagementsuite_db_hostname_string'] = wp_filter_nohtml_kses($input['trinitymanagementsuite_db_hostname_string']);
	$input['trinitymanagementsuite_db_user_string'] = wp_filter_nohtml_kses($input['trinitymanagementsuite_db_user_string']);
	$input['trinitymanagementsuite_db_pass_string'] = wp_filter_nohtml_kses($input['trinitymanagementsuite_db_pass_string']);

	$input['trinitymanagementsuite_db_world_text_string'] = wp_filter_nohtml_kses($input['trinitymanagementsuite_db_world_text_string']);
	$input['trinitymanagementsuite_db_char_text_string'] = wp_filter_nohtml_kses($input['trinitymanagementsuite_db_char_text_string']);
	$input['trinitymanagementsuite_db_auth_text_string'] = wp_filter_nohtml_kses($input['trinitymanagementsuite_db_auth_text_string']);

	$input['trinitymanagementsuite_world_port_string'] = wp_filter_nohtml_kses($input['trinitymanagementsuite_world_port_string']);
	$input['trinitymanagementsuite_auth_port_string'] = wp_filter_nohtml_kses($input['trinitymanagementsuite_auth_port_string']);

	// Return validated input
	return $input;
}

/* When this plugin is deactivated, remove the options as well */
function trinitymanagementsuite_remove()
{
	$tmp = get_option('trinitymanagementsuite_plugin_options');

    delete_option('trinitymanagementsuite_recaptcha_apikey_pub_string');
    delete_option('trinitymanagementsuite_recaptcha_apikey_priv_string');
    
	delete_option('trinitymanagementsuite_db_hostname_string');
	delete_option('trinitymanagementsuite_db_user_string');
	delete_option('trinitymanagementsuite_db_pass_string');

	delete_option('trinitymanagementsuite_db_world_text_string');
    delete_option('trinitymanagementsuite_db_char_text_string');
	delete_option('trinitymanagementsuite_db_auth_text_string');

	delete_option('trinitymanagementsuite_world_port_string');
    delete_option('trinitymanagementsuite_auth_port_string');
}

/* Define default option settings */
function trinitymanagementsuite_add_defaults()
{
	global $options_array;
	$tmp = get_option('trinitymanagementsuite_plugin_options');

    $options_array = array(
        "trinitymanagementsuite_recaptcha_apikey_priv_string"    =>  "YOUR_RECAPTHA_PRIVATE_KEY",
        "trinitymanagementsuite_recaptcha_apikey_pub_string"    =>  "YOUR_RECAPTCHA_PUBLIC_KEY",
        "trinitymanagementsuite_db_hostname_string"	=>	"localhost",
        "trinitymanagementsuite_db_user_string"		=>	"username",
        "trinitymanagementsuite_db_pass_string"		=>	"securepasssword",
        "trinitymanagementsuite_db_world_text_string"	=>	"world",
        "trinitymanagementsuite_db_char_text_string"	=>	"characters",
        "trinitymanagementsuite_db_auth_text_string"	=>	"auth",
        "trinitymanagementsuite_world_port_string"	=>	"8085",
        "trinitymanagementsuite_auth_port_string"	=>	"3724"
    );
	foreach( $options_array as $k => $v )
	{
		update_options($k, $v);
	}
	return;

	update_option('trinitymanagementsuite_plugin_options', $options_array);
}

?>
