<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';

abstract class Fitet_Monitor_Shortcode extends Fitet_Monitor_Component {

	public $tag;
	private $data;

	public function __construct($version, $plugin_name, $tag) {
		parent::__construct($this->plugin_name, $version);
		$this->tag = $tag;
	}

	public function initialize() {
		parent::initialize();
	}

	public final function render_shortcode($attributes, $content = null) {
		$attributes = empty($attributes) ? [] : $attributes;
		$content = do_shortcode($content);
		$attributes = $this->merge_attributes($attributes);
		$this->data = $this->process_attributes($attributes);
		$this->initialize();
		$this->data['content'] = $content;
		$render = $this->render($this->data);
		memory_dump();
		return $render;
	}

	protected function load_components() {
		$this->components = $this->shortcode_components($this->data);
	}

	protected function components() {
		throw new Exception("Not callable from Shortcode class");
	}

	protected abstract function wrapped_component($mode);


	private function shortcode_components($data) {
		return ['component' => $this->wrapped_component($data['mode'])];
	}

	protected function process_attributes($attributes) {
		return $attributes;
	}

	protected function process_data($data) {
		return [
			'head' => $data['content'],
			'body' => $this->components['component']->render($data['data'])
		];
	}

	protected function template() {
		return "<div class='fm-shortcode'><div class='fm-shortcode-head'>{{head}}</div><div class='fm-shortcode-body'>{{body}}</div></div>";
	}


	public function initialize_rest_api() {
		add_action('rest_api_init', function () {
			register_rest_route($this->plugin_name . '/v1', '/shortcode/' . $this->tag,
				['methods' => 'GET',
					'callback' => [$this, 'rest_api'],
					'permission_callback' => function () {
						return current_user_can('edit_posts');
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


	protected final function parse_query_params() {
		$configuration = [];
		foreach ($this->attributes() as $attribute) {
			$configuration[$attribute] = get_query_var($attribute);
		}
		return $configuration;
	}

	protected function attributes() {
		return [];
	}


	protected function defaults(): array {
		$defaults = [];
		foreach ($this->attributes() as $attribute) {
			$defaults[$attribute] = '';
		}
		return $defaults;
	}

	protected final function merge_attributes($attributes) {
		// loading default configuration
		$configuration = $this->defaults();
		// loading params configuration
		$configuration = array_merge($configuration, $this->parse_query_params());
		// overwriting configuration with shortcode parameters
		$configuration = shortcode_atts($configuration, $attributes, $this->tag);
		return $configuration;
	}


}
