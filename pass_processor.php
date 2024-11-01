<?php

/*  Copyright 2012  Kotori  (email: kotori@deimos.hopto.org)

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

/**
 * 1.) Generate new random password.
 * 2.) Assign new password SHA1 to TrinityCore.
 * INSERT INTO ACCOUNT (username, sha_pass_hash, email, expansion) VALUES ('username', LOWER(SHA1(UPPER(CONCAT('username', ':', 'password')))), 'email', 2)
 *
 * 3.) Assign new password MD5 to Wordpress.
 * UPDATE wp_users SET user_pass = MD5('newpassword') WHERE user_login = "admin";
 * OR
 * $user_id = 1;
 * $new_pass = 'HelloWorld';
 * wp_set_password($new_pass, $user_id);
 *
 * 4.) Send email starting new credentials.
 */

echo '<h2>Password Management</h2>';

if ( is_user_logged_in() )
{
    include( plugin_dir_path( __FILE__ ) . 'wow_funcs/db_sanitizer.php' );

    /* Setup the MySQL connection with settings from the admin page */
    $user_options = get_option('trinitymanagementsuite_plugin_options');

    global $current_user;
    get_currentuserinfo();

    $plugindir =  str_replace(basename(__FILE__),"",__FILE__);
    $plugindir = str_replace('\\','/',$plugindir);
    $plugindir = rtrim($plugindir,"/");

    $ipi = getenv("REMOTE_ADDR");
    $httprefi = getenv ("HTTP_REFERER");
    $httpagenti = getenv ("HTTP_USER_AGENT");

    /* Check to see if this script was posted to from the form */
    $post = (!empty($_POST)) ? true : false;

    /*
     * If the form has been posted to, validate the form for pass change.
     */
    if($post && isset($_POST['thepassword']) && isset($_POST['recaptcha_response_field']))
    {
        /* the error code from reCAPTCHA, if any */
        $error = null;

        /* Set the key values from our wordpress options and POST values */
        $privatekey = $user_options['trinitymanagementsuite_recaptcha_apikey_priv_string'];

        /* Captcha Values */
        $post_thecaptchachallenge = stripslashes($_POST['recaptcha_challenge_field']);
        $post_thecaptcharesponse = stripslashes($_POST['recaptcha_response_field']);

        /* The actual password */
        $post_thepassword = stripslashes($_POST['thepassword']);

        /* A little information about the host that posted to the form */
        $post_client_ip = ($_POST['ip']);
        $post_client_referrer = ($_POST['httpref']);
        $post_client_agent = ($_POST['httpagent']);
        
        $this_user = $current_user->user_login;
        
        //echo 'Client IP: '. $post_client_ip .'<br />';
        
        /* Check the response with the Captcha */
        $resp = recaptcha_check_answer(
            $privatekey,
            $_SERVER["REMOTE_ADDR"],
            $_POST["recaptcha_challenge_field"],
            $_POST["recaptcha_response_field"]
        );
               
        if ($resp->is_valid)
        {
            $manage_password_mysqli_conn = new mysqli(
                $user_options['trinitymanagementsuite_db_hostname_string'],  /* Hostname */
                $user_options['trinitymanagementsuite_db_user_string'],      /* Username */
                $user_options['trinitymanagementsuite_db_pass_string'],      /* Password */
                $user_options['trinitymanagementsuite_db_auth_text_string']  /* Database */
            );
            
            /* Check MySQL connection */
            if ($manage_password_mysqli_conn->connect_errno)
            {
                error_log('MySQL Error: ' . $manage_password_mysqli_conn->connect_error, 0);
            }
            else
            {
                //echo 'Checking for matching player in database...<br />';
                /* Convert the passed password into a valid login string */
                $wow_password = trinitymanager_sha_password( $this_user, $post_thepassword );

                $wow_password = $manage_password_mysqli_conn->real_escape_string($wow_password);
                $blog_password = $post_thepassword;
                $this_user = $manage_password_mysqli_conn->real_escape_string($this_user);

                $wow_sql = "UPDATE `account` SET `sha_pass_hash` = '$wow_password' WHERE `username` = '$this_user'";
                $blog_sql = "UPDATE `wp_users` SET `user_pass` = MD5('$blog_password') WHERE `user_login` = '$this_user'";
                
                echo '<font color="blue">[DEBUG]</font><br />';
                echo '<font color="blue">Account User: </font>'. $this_user .'<br />';
                echo '<font color="blue">WoW Password: </font>'. $wow_password .'<br />';
                echo '<font color="blue">Blog Password: </font>'. $blog_password .'<br />';
                echo '<font color="blue">SQL INSERTS: </font>'. $sql .'<br />';
                            
                /* Will return true if sucessful else it will return false */
                if($manage_password_mysqli_conn->query($wow_sql))
                {
                    if ($manage_password_mysqli_conn->affected_rows==1)
                    {
                        /** TODO
                         * Now that we've successfully updated our TrinityCore password,
                         *  we should also update the blog password as well.
                         */
                        global $wowdb;
                        $hash = wp_hash_password( $post_thepassword );
                        $user_id = $current_user->ID;
                        $wowdb->update($wowdb->users, array('user_pass' => $hash, 'user_activation_key' => ''), array('ID' => $user_id) );

                        wp_cache_delete( $user_id, 'users' );

                        /* Finish inserting new password into Wordpress */

                        echo '<font color="green">Success!</font><br />';
                        echo 'Your New Password: <b>'. $thepassword .'</b><br />';
                        
                        $admin_email = get_option('admin_email');
                        $from = '<'. $admin_info->user_login .'> '. $admin_email;
                        $to = $current_user->user_email;
                        $cc =  '<'. $admin_info->user_login .'> '. $admin_email;
                        $subject = 'Password Reset';
                        $message = 'Your password has been reset via the player change form.\n
                                    Please report this immediately if you did not perform this action!\n
                                    Your password was reset from the the IP: '. $post_client_ip .', Agent: '. $post_client_agent .'\n\n
                                    Thank You,\n
                                    Administration';
                        
                        /* Build the email headers */
                        $headers[] .= "Content-Type: text/html; charset=utf-8\r\n";
                        $headers[] .= 'Cc: '. $cc . "\r\n";
                        $headers[] .= 'From: '. $from . "\r\n";
                        $headers[] .= 'Reply-To: ' . $from . "\r\n";
                        $headers[] .= "X-Priority: 1\r\n";
                        $headers[] .= 'X-Mailer: PHP';

                        /* Actually send out the password change email */
                        wp_mail( $to, $subject, $message, $headers );
                    }
                    else
                    {
                        echo '<font color="red">UPDATE Failure. No Matching User Found!</font><br />';
                    }
                }
                else
                {
                    // 
                }
            }
            
            $manage_password_mysqli_conn->close();
        }
        else
        {
            echo '<font color="red">Invalid Captcha</font><br />';
            $error = $resp->error;
        }
        
        /* If the error string is not empty, display it */
        if($error)
        {
            //echo $error .'<br /><br />';
        }
    }


    /* 
     * Now that we've passed our post detection, go ahead and create the form 
     */

    $manage_password_mysqli_conn = new mysqli(
        $user_options['trinitymanagementsuite_db_hostname_string'],  /* Hostname */
        $user_options['trinitymanagementsuite_db_user_string'],      /* Username */
        $user_options['trinitymanagementsuite_db_pass_string'],      /* Password */
        $user_options['trinitymanagementsuite_db_auth_text_string']  /* Database */
    );

    /* Check MySQL connection */
    if ($manage_password_mysqli_conn->connect_errno)
    {
        echo '<b>Error Establishing a database connection.</b>';
    }
    else
    {
        $recaptcha_key = $user_options['trinitymanagementsuite_recaptcha_apikey_pub_string'];

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

        $sql_query = sprintf(__("SELECT `id` FROM `account` WHERE `username`='%s'"),
            mysql_real_escape_string($current_user->user_login));

        $manage_password_mysqli_conn->real_query( $sql_query );
        if( $manage_password_mysqli_conn->field_count )
        {
            $sql_result = $manage_password_mysqli_conn->store_result();
            /* If sql_result is true, we have a matching user, so print the change password form */
            if( $sql_result )
            {
                $row_cnt = $sql_result->num_rows;
                if( !$row_cnt > 0 )
                {                    
                    $row = $sql_result->fetch_assoc();

                    /* First make sure our user actually has access to change a password */
                    echo 'Greetings ' . $current_user->user_login . ',<br />';
                    echo 'Type in your NEW password twice, once accepted this will change your website and WoW credentials <br />';
                    echo 'Please note that you will need to logout and back in after this change <br />'; 
                    echo '<br />Password Guidelines:';
                    echo '<ul><li><b>Minimum Length:</b> 6 Characters</li>';
                    echo '<li><b>Format:</b> at least one number, one lowercase and one uppercase letter, no spaces</li>';
                    echo '<li><b>Please note</b> your IP is being logged.</ul><p>';
            
                    /* Begin the password reset form */
                    ?>
                    <form name="passchange_form" action="" onsubmit="return ValidateCompleteForm(this);" method="post" >
                        
                        <input type="hidden" name="ip" value="<?php echo $ipi ?>" />
                        <input type="hidden" name="httpref" value="<?php echo $httprefi ?>" />
                        <input type="hidden" name="httpagent" value="<?php echo $httpagenti ?>" />
                        
                                <table>
                                    <tr>
                                        <td>
                                                <!-- New Password -->
                                                <label for="thepassword">Password</label>
                                        </td>
                                        <td>
                                                <input name="thepassword" id="thepassword" required class="thepassword" placeholder="Password" type="password" tabindex="1" size="25" value="" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                                <!-- New Password Repeated-->
                                            <label for="thepasswordrepeat">Repeat Password</label>
                                        </td>
                                        <td>
                                                <input name="thepasswordrepeat" id="thepasswordrepeat" required class="thepasswordrepeat" placeholder="Repeat Password" type="password" tabindex="2" size="25" value="" />
                                        </td>
                                    </tr>
                                </table>
                        
                        <!-- Captcha Response-->
                        <label for="recaptcha_response_field"></label>
                        <?php echo recaptcha_get_html($recaptcha_key, $error, true); ?>
                        
                        <div id="recaptcha_widget" style="display:none">
                            <div><a href="javascript:Recaptcha.reload()">Refresh CAPTCHA</a></div>
                            <div id="recaptcha_image"></div>
                            
                            <!-- <div><a href="javascript:Recaptcha.reload()">New CAPTCHA</a></div> -->
                            <div class="recaptcha_only_if_audio"><a href="javascript:Recaptcha.switch_type('image')">image CAPTCHA</a></div>
                            
                            <span class="recaptcha_only_if_image">Captcha:</span>

                            <input type="text" id="recaptcha_response_field" name="recaptcha_response_field" />
                        </div>
                        
                        <!-- Captcha Challenge-->
                        <?php echo '<script type="text/javascript" src="'. $recaptcha_challenge_url .'"> </script>'; ?>

                        <noscript>
                            <?php echo '<iframe src="'. $recaptcha_noscript_url .'"height="100" width="200" ></iframe><br>'; ?>
                            <textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
                            <input type="hidden" name="recaptcha_response_field" value="manual_challenge">
                        </noscript>
                        
                        <!-- Submit Button -->
                        <input class="button-primary" type="submit" name="btnSubmit" value="<?php esc_attr_e('Update'); ?>"/>
                    </form> 
                    <?php
                    /* End the password reset form */
                }
                else
                {
                    echo '<b>ERROR:</b> No Player found matching your ID!<br />';
                }
            }
            mysqli_free_result( $sql_result );
        }
    }

    $manage_password_mysqli_conn->close();
}
else
{
    echo 'Welcome, Visitor!<p>You must login before you can use this feature.';
}




?>
