<?php

/*  Simple Realm Status Widget  */

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

class RealmStatusWidget extends WP_Widget
{
  /**
    * Register widget with WordPress.
    */
  function RealmStatusWidget()
  {
    $widget_ops = array('classname' => 'RealmStatusWidget', 'description' => 'Displays the realm status of a private WoW server' );
    $this->WP_Widget('RealmStatusWidget', 'Realm Status', $widget_ops);
  }

  /**
    * Back-end widget form.
    *
    * @see WP_Widget::form()
    *
    * @param array $instance Previously saved values from database.
    */
  function form($instance)
  {
    /* Set up some default widget settings. */
    $defaults = array( 'title' => 'My Realm', 'trinity_server_hostname' => 'localhost', 'trinity_server_port' => '8085' );
    $instance = wp_parse_args( (array) $instance, $defaults );
?>
	<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>">Realm Title:</label>
		<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
	</p>
	<p>
		<label for="<?php echo $this->get_field_id( 'trinity_server_hostname' ); ?>">Realm Host:</label>
		<input id="<?php echo $this->get_field_id( 'trinity_server_hostname' ); ?>" name="<?php echo $this->get_field_name( 'trinity_server_hostname' ); ?>" value="<?php echo $instance['trinity_server_hostname']; ?>" style="width:100%;" />
	</p>
	<p>
                <label for="<?php echo $this->get_field_id( 'trinity_server_port' ); ?>">Realm Port:</label>
                <input id="<?php echo $this->get_field_id( 'trinity_server_port' ); ?>" name="<?php echo $this->get_field_name( 'trinity_server_port' ); ?>" value="<?php echo $instance['trinity_server_port']; ?>" style="width:100%;" />
        </p>
<?php
  }

  /**
    * Sanitize widget form values as they are saved.
    *
    * @see WP_Widget::update()
    *
    * @param array $new_instance Values just sent to be saved.
    * @param array $old_instance Previously saved values from database.
    *
    * @return array Updated safe values to be saved.
    */
  function update($new_instance, $old_instance)
  {
    $instance = $old_instance;

    $instance['title'] = strip_tags( $new_instance['title'] );
    $instance['trinity_server_port'] = strip_tags( $new_instance['trinity_server_port'] );
    $instance['trinity_server_hostname'] = strip_tags( $new_instance['trinity_server_hostname'] );

    return $instance;
  }

  /**
    * Front-end display of widget.
    *
    * @see WP_Widget::widget()
    *
    * @param array $args     Widget arguments.
    * @param array $instance Saved values from database.
    */
  function widget($args, $instance)
  {
    extract($args, EXTR_SKIP);

    /* User-selected values from options */

    echo $before_widget;
    $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);

    $trinity_server_hostname = $instance['trinity_server_hostname'];
    $trinity_server_port = $instance['trinity_server_port'];

    /* Before widget (defined by theme) */
    echo $before_widget;

    /* Title of widget (before and after defined by themes). */
    if (!empty($title))
      echo $before_title . $title . $after_title;;

    /* Begin ping code. */
    $timeoutInSeconds = 1;
    echo 'Status: ';
    if( $fp = fsockopen( $trinity_server_hostname, $trinity_server_port, $errCode, $errStr, $timeoutInSeconds ) )
    {
      echo '<img width="32" height="32" style=vertical-align:middle name="online" alt="online" src="' .plugins_url('images/online.png', __FILE__).'" border="0" />';
    }
    else
    {
      echo '<img width="32" height="32" style=vertical-align:middle name="offline" alt="online" src="' .plugins_url('images/offline.png', __FILE__).'" border="0" />';
    }
    fclose($fp);
    /* End ping code. */

    echo $after_widget;
  }

}
add_action( 'widgets_init', create_function('', 'return register_widget("RealmStatusWidget");') );

?>
