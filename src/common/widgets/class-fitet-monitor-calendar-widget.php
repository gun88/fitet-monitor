<?php

// Creating the widget
class Fitet_Monitor_Calendar_Widget extends WP_Widget {

	function __construct() {
		$id = __('WPBeginner Widget', 'fitet-monitor');
		$description = __('Sample widget based on WPBeginner Tutorial', 'fitet-monitor');
		parent::__construct('Fitet_Monitor_Calendar_Widget', $id, ['description' => $description,]);
	}

	public function widget($args, $instance) {
		$title = apply_filters('widget_title', $instance['title']);

		echo $args['before_widget'];
		if (!empty($title))
			echo $args['before_title'] . $title . $args['after_title'];

// This is where you run the code and display the output
		echo $this->content();
		echo $args['after_widget'];
	}

// Widget Backend
	public function form($instance) {
		if (isset($instance['title'])) {
			$title = $instance['title'];
		} else {
			$title = __('New title', 'fitet-monitor');
		}
// Widget admin form
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'fitet-monitor'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
				   name="<?php echo $this->get_field_name('title'); ?>" type="text"
				   value="<?php echo esc_attr($title); ?>"/>
		</p>
		<?php
	}

// Updating widget replacing old instances with new
	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
		return $instance;
	}

// Class Fitet_Monitor_Calendar_Widget ends here

    /**
     * @return mixed
     */
    public function content() {
        return "dsadsadsa";
    }
}

