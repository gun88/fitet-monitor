<?php

require_once FITET_MONITOR_DIR . 'public/includes/class-fitet-monitor-shortcode.php';

class Fitet_Monitor_Sample_Shortcode extends Fitet_Monitor_Shortcode {

	private $plugin_name;

	public function __construct($version, $plugin_name) {
		parent::__construct($version);
		$this->plugin_name = $plugin_name;
	}


	public function render_shortcode($attributes, $content = null) {
		$default = [
			'style' => '',
		];
		$style = shortcode_atts($default, $attributes)['style'];
		$name = do_shortcode($content);
		$name = !empty($name) ? $name : 'NOT_SET';

		$from = get_option('fitet-monitor-club-code');
		$str = sprintf(__("Hello %s! Regards from %s", $this->plugin_name, 'fitet-monitor'), $name, $from);
		return "<p style='$style'>" . $str . "</p>";
	}
}
