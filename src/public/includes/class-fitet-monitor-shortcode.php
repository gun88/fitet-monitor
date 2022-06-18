<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';

class Fitet_Monitor_Shortcode extends Fitet_Monitor_Component {

	public $tag;
	private $plugin_name;

	public function __construct($version, $plugin_name, $tag) {
		parent::__construct($version);
		$this->tag = $tag;
		$this->plugin_name = $plugin_name;
	}

	public function initialize() {
		parent::initialize();
		$this->initialize_rest_api();
	}

	public function render_shortcode($attributes, $content = null) {
		$content = do_shortcode($content);
		return $this->render(['attributes' => $attributes, 'content' => $content]);
	}

	public function initialize_rest_api() {
		add_action('rest_api_init', function () {
			register_rest_route($this->plugin_name . '/v1', '/shortcode/' . $this->tag,
				['methods' => 'GET',
					'callback' => [$this, 'rest_api'],
					'permission_callback' => function () {
						return current_user_can('manage_options');
					}
				]
			);
		});
	}

	public function rest_api(WP_REST_Request $request) {
		$attributes = $request->get_params();
		$content = $attributes['content'];
		unset($attributes['content']);
		return rest_ensure_response(['body' => $this->render_shortcode($attributes, $content)]);
	}

}
