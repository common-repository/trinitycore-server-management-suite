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

echo '<h2>Player Listing</h2>';

if ( is_user_logged_in() )
{

    /* Setup the MySQL connection with settings from the admin page */
    $trinity_options = get_option('trinitymanagementsuite_plugin_options');

    $mysqli_conn_playerlist = new mysqli(
        $trinity_options['trinitymanagementsuite_db_hostname_string'],	// Hostname
        $trinity_options['trinitymanagementsuite_db_user_string'],	// Username
        $trinity_options['trinitymanagementsuite_db_pass_string'],	// Password
        $trinity_options['trinitymanagementsuite_db_char_text_string']	// Database
    );

    /* Check MySQL connection */
    if ( !$mysqli_conn_playerlist->connect_errno )
    {

        $sql_result = $mysqli_conn_playerlist->query( mysql_real_escape_string("SELECT * FROM characters WHERE online > 0") );

        if( $sql_result )
        {
            // Determine number of rows result set
            $row_cnt = $sql_result->num_rows;
            echo '<center>';
            if($row_cnt == 0)
            {
                echo '<b>Server Empty</b><br>';
                echo '</center>';
            }
            else
            {
                echo "<b>($row_cnt) Players Online:</b><br><br>";
                echo '<table style="border:none;padding:0;">';

                echo '<th style="vertical-align:bottom;padding-right:10px;">Account</th>';
                echo '<th style="vertical-align:bottom;padding-right:10px;">Level</th>';
                echo '<th style="vertical-align:bottom;padding-right:10px;">Name</th>';
                echo '<th style="vertical-align:bottom;padding-right:10px;">Race</th>';
                echo '<th style="vertical-align:bottom;padding-right:10px;">Class</th>';
                echo '<th style="vertical-align:bottom;padding-right:10px;">Faction</th>';

                while( $row = $sql_result->fetch_assoc() )
                {
                    echo '<tr>';

                    echo '<td style="vertical-align:bottom;padding-right:10px;">' . $row['account'] . '</td>';
                    echo '<td style="vertical-align:bottom;padding-right:10px;">' . $row['level'] . '</td>';
                    echo '<td style="vertical-align:bottom;padding-right:10px;">' . $row['name'] . '</td> ';
                    echo '<td style="vertical-align:bottom;padding-right:10px;">' . $character_race[$row['race']] . '</td>';
                    echo '<td style="vertical-align:bottom;padding-right:10px;">' . $character_class[$row['class']] . '</td>';

                    if($row['race'] == 4 || $row['race'] == 5 || $row['race'] == 6 || $row['race'] == 8 || $row['race'] == 10)
                    {
                        echo '<td style="vertical-align:bottom;padding-right:10px;">' . '<img name="Horde" src="' . get_bloginfo('stylesheet_directory') . '/images/faction_horde.png" border="0" height="32" width="32"/>' . '</td>';
                    }
                    else
                    {
                        echo '<td style="vertical-align:bottom;padding-right:10px;">' . '<img name="Alliance" src="' . get_bloginfo('stylesheet_directory') . '/images/faction_alliance.png" border="0" height="32" width="32" />' . '</td>';
                    }

                    echo '</tr>';
                }
                echo '</table></center><br>';
            }
        }
        else
        {
            echo '<center><b>Unable to Execute SQL Query.</b><p>';
            echo '<b>Ensure you have specified the corrent database</b></center>';
        }
         $sql_result->close();

    }

    $mysqli_conn_playerlist->close();
}
else
{
    echo 'Welcome, Visitor!<p>You must login before you can use this feature.';
}

?>
