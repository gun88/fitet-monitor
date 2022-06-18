<?php

require_once FITET_MONITOR_DIR . 'public/includes/class-fitet-monitor-shortcode.php';

class Fitet_Monitor_Sample_Shortcode extends Fitet_Monitor_Shortcode {

	private $plugin_name;

	public function __construct($version, $plugin_name) {
		parent::__construct($version, $plugin_name, 'subscribe');
		$this->plugin_name = $plugin_name;
	}

	protected function process_data($data) {
		$attributes = $data['attributes'];
		$content = $data['content'];

		$style = shortcode_atts(['style' => ''], $attributes, $this->tag)['style'];

		$name = !empty($content) ? $content : 'NOT_SET';

		$from = get_option('fitet-monitor-club-code');

		$message = sprintf(__("Hello %s! Regards from %s", $this->plugin_name, 'fitet-monitor'), $name, $from);

		return ['style' => $style, 'message' => $message,];
	}


}
