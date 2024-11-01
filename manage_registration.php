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

function trinitymanager_check_symbols($string)
{
        $len=strlen($string);
        $allowed_chars="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        for($i=0;$i<$len;$i++)
        {
            if(!strstr($allowed_chars,$string[$i]))
            {
                return TRUE;
            }
        }
        return FALSE;
}

/* This function will convert a username and password into a SHA password for TrinityCore insertion */
function trinitymanager_sha_password($user, $pass)
{
        $user = strtoupper($user);
        $pass = strtoupper($pass);

        return SHA1($user.':'.$pass);
}

/* When the registration page is submitted, this function is called */
add_action('register_post','trinitymanager_plugin_post', 10, 3);
function trinitymanager_plugin_post($login, $email, $errors)
{
    /* Ensure that when this function is called, its not due to a page refresh */
    /* Check to see if this script was posted to from the form */
    $ispost = ( !empty($_POST) ) ? true : false;

    if ( !$ispost && empty($_POST["recaptcha_response_field"]) && empty($_POST['password']) && empty($_POST['password_repeat']) )
    {
        return $errors->add( 'invalid_post', "<strong>ERROR</strong>: Please fill out the form below!" );
    }

	/* Setup with settings from the admin page */
	$adminoptions = get_option('trinitymanagementsuite_plugin_options');
	$recaptcha_key = $adminoptions['trinitymanagementsuite_recaptcha_apikey_pub_string'];
	$privatekey = $adminoptions['trinitymanagementsuite_recaptcha_apikey_priv_string'];

	/* Check the response with the Captcha */
    $resp = recaptcha_check_answer(
        $privatekey,
        $_SERVER["REMOTE_ADDR"],
        $_POST["recaptcha_challenge_field"],
        $_POST["recaptcha_response_field"]
	);

	if (!$resp->is_valid)
	{
		return $errors->add( 'captcha_error', "<strong>ERROR</strong>: Invalid Captcha!" );
	}
	else
	{
        $pwd = $_POST['password'];
        $rpwd = $_POST['password_repeat'];
        
        /* These if else statements ensure the password is upto TrinityCore auth standards */
        if ( $pwd == '' )
        {
            return $errors->add( 'password_empty', "<strong>ERROR</strong>: You must enter a password!" );
        }
        /* Ensure the password matches the repeated password */
        if ( $pwd !== $rpwd )
		{
		        return $errors->add( 'passwords_not_matched', "<strong>ERROR</strong>: Passwords must match!" );
		}
		/* Ensure the password is greater than $MIN_PASS_CHARS */
		if ( strlen( $pwd ) < 8 )
		{
		        return $errors->add( 'password_too_short', '<strong>ERROR</strong>: Password must be at least 8 characters long!' );
		}
        /* Ensure the password contains at least one letter */
        if ( !preg_match("#[a-z]+#", $pwd) )
        {
            return $errors->add( 'password_complexity', "<strong>ERROR</strong>: Password is too weak!<p>Include at least one letter." );
        }
        /* Ensure the password contains at least one CAPITAL letter */
        if ( !preg_match("#[A-Z]+#", $pwd) )
        {
            return $errors->add( 'password_complexity', "<strong>ERROR</strong>: Password is too weak!<p>Include at least one capital letter." );
        }
        /* Ensure the password contains at least one number */
        if (  !preg_match("#[0-9]+#", $pwd) )
        {
            return $errors->add( 'password_complexity', "<strong>ERROR</strong>: Password is too weak!<p>Include at least one number." );
        }
		/* Ensure the password does not contain any symbols */
		if ( trinitymanager_check_symbols( $pwd ) == TRUE )
		{
		        return $errors->add( 'password_invalid_symbols', "<strong>ERROR</strong>: Password has invalid symbols in it!<p>Alphanumeric characters only." );
		}

        if ( $errors->get_error_code() )
        {
            return $errors->add( 'error_generic', "Please correct the errors above before continuing." );
        }
        else
        {
            /* Actual MySQLi connection string */
            $sql_connect = new mysqli(
                $adminoptions['trinitymanagementsuite_db_hostname_string'],
                $adminoptions['trinitymanagementsuite_db_user_string'],
                $adminoptions['trinitymanagementsuite_db_pass_string'],
                $adminoptions['trinitymanagementsuite_db_auth_text_string']
            );

            /* check connection */
            if ($sql_connect->connect_errno)
            {
                return $errors->add( 'sql_db_error', "<strong>ERROR</strong>: " . $sql_connect->connect_error );
            }
            else
            {
                $expansionnumber = $_POST['expansion'];
                $emailaddy = $_POST['user_email'];
                $wow_pwd = trinitymanager_sha_password( $login, $pwd );
        
                /* Ensure the username doesn't already exist */
                $check_username_query = sprintf("SELECT username FROM account WHERE username='%s'", mysql_real_escape_string($login));

                $username_exists = $sql_connect->query($check_username_query);

                if( !$username_exists )
                {
                        return $errors->add( 'sql_username_exists', "<strong>ERROR</strong>: Error with creating account!<p>Username already exists in WoW database." );
                }
                else if( !$errors->get_error_code() )
                {
                    if ($sql_connect->query("INSERT INTO account (username,sha_pass_hash,email,expansion) VALUES('$login','$wow_pwd','$emailaddy','$expansionnumber')") === FALSE)
                    {
                        return $errors->add( 'sql_generic_error', "<strong>ERROR</strong>: " . $sql_connect->error );
                    }
                }
                else
                {
                        return $errors->add( 'sql_generic_error', "<strong>ERROR</strong>: A SQL Error has prvented this regisration." );
                }

                $username_exists->close();
                $sql_connect->close();
            }
        }
	}
}

add_action( 'user_register', 'insert_register_fields' );
function insert_register_fields( $user_id )
{
     
    //
}

// Storing WordPress user-selected password into database on registration
// http://wp.me/p1Ehkq-gn

add_action( 'user_register', 'ts_register_extra_fields', 100 );
function ts_register_extra_fields( $user_id )
{
    $userdata = array();

    $userdata['ID'] = $user_id;
    $userdata['user_pass'] = $_POST['password'];
    
    $new_user_id = wp_update_user( $userdata );
    
    update_user_option( $user_id, 'default_password_nag', false, true );
}

// Editing WordPress registration confirmation message
// http://wp.me/p1Ehkq-gn

add_filter( 'gettext', 'ts_edit_password_email_text' );
function ts_edit_password_email_text ( $text )
{
    if ( $text == 'A password will be e-mailed to you.' )
    {
        $text = '';
    }
    return $text;
}


add_action('register_form','trinitymanager_plugin_form');
function trinitymanager_plugin_form()
{
	/* Setup with settings from the admin page */
	$adminoptions = get_option('trinitymanagementsuite_plugin_options');
	$recaptcha_key = $adminoptions['trinitymanagementsuite_recaptcha_apikey_pub_string'];
	$privatekey = $adminoptions['trinitymanagementsuite_recaptcha_apikey_priv_string'];

	if ($_SERVER['HTTPS'] == "on")
	{
		$recaptcha_challenge_url = 'https://www.google.com/recaptcha/api/challenge?k=' . $recaptcha_key .'&error=incorrect-captcha-sol';
		$recaptcha_noscript_url = 'https://www.google.com/recaptcha/api/noscript?k=' . $recaptcha_key;
	}
	else
	{
		$recaptcha_challenge_url = 'http://www.google.com/recaptcha/api/challenge?k=' . $recaptcha_key .'&error=incorrect-captcha-sol';
		$recaptcha_noscript_url = 'http://www.google.com/recaptcha/api/noscript?k=' . $recaptcha_key;
	}
?>
<p>
	<div width="100%">
		<p>
			<label><?php _e('Password') ?></label><br/>
                <input id="password" class="input" type="password" tabindex="25" size="30" value="" name="password" />
            </label>
        </p>

        <p>
            <label><?php _e('Repeat Password') ?></label><br/>
                <input id="password_repeat" class="input" type="password" tabindex="30" size="30" value="" name="password_repeat" />
            </label>

        </p>
        
        <p>
			<label><?php _e('Client Expansion') ?></label><br/>
				<select name="expansion" tabindex="40">
					<option value="1">Vanilla WoW</option>
					<option value="2">The Burning Crusade</option>
					<option value="3">Wrath of the Lich King</option>
				</select>
			</label>
		</p><br/>

		<p>
            <div>
                <!-- BEGIN CAPTCHA -->
                <label><?php _e('Captcha') ?></label><br/>
                    <?php echo '<script type="text/javascript" src="'. $recaptcha_challenge_url .'"> </script>'; ?>
                    <noscript>
                        <?php echo '<iframe src="'. $recaptcha_noscript_url .'" width="500" frameborder="0"></iframe><br>'; ?>
                        <textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
                        <input type="hidden" name="recaptcha_response_field" value="manual_challenge">
                    </noscript>
                <!-- END CAPTCHA -->
            </div>
        </p>
	</div>
<?php
}


?>
