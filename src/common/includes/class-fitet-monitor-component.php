<?php

require_once FITET_MONITOR_DIR . 'admin/includes/class-fitet-monitor-page.php';

class Fitet_Monitor_Component {

	protected $components = [];

	protected $version;

	protected $template = "{{content}}";


	/**
	 * @param string $version
	 */
	public function __construct($version) {
		$this->version = $version;

	}

	public function enqueue_styles() {

		$file = (new ReflectionClass($this))->getFileName();

		$default_css_file_path = plugin_dir_path($file) . basename($file, '.php') . '.min.css';
		if (file_exists($default_css_file_path)) {
			$default_css_file_url = plugin_dir_url($file) . basename($file, '.php') . '.min.css';
			wp_enqueue_style(get_class($this), $default_css_file_url, [], $this->version, 'all');
		}

		$default_css_file_path = plugin_dir_path($file) . basename($file, '.php') . '.css';
		if (file_exists($default_css_file_path)) {
			$default_css_file_url = plugin_dir_url($file) . basename($file, '.php') . '.css';
			wp_enqueue_style(get_class($this), $default_css_file_url, [], $this->version, 'all');
		}
	}

	public function enqueue_scripts() {

		$file = (new ReflectionClass($this))->getFileName();

		$default_js_file_path = plugin_dir_path($file) . basename($file, '.php') . '.min.js';
		if (file_exists($default_js_file_path)) {
			$default_js_file_url = plugin_dir_url($file) . basename($file, '.php') . '.min.js';
			wp_enqueue_script(get_class($this), $default_js_file_url, ['jquery'], $this->version, 'all');
		}

		$default_js_file_path = plugin_dir_path($file) . basename($file, '.php') . '.js';
		if (file_exists($default_js_file_path)) {
			$default_js_file_url = plugin_dir_url($file) . basename($file, '.php') . '.js';
			wp_enqueue_script(get_class($this), $default_js_file_url, ['jquery'], $this->version, 'all');
		}
	}

	private function load_components() {
		$this->components = $this->components();
	}

	private function load_template() {
		$file = (new ReflectionClass($this))->getFileName();
		$template_path = plugin_dir_path($file) . basename($file, '.php') . '.html';
		if (file_exists($template_path)) {
			$this->template = file_get_contents($template_path);
		}
	}

	public final function initialize() {
		$this->load_components();
		$this->load_template();
		$this->enqueue_scripts();
		$this->enqueue_styles();
		foreach ($this->components as $component) {
			$component->initialize();
		}

	}

	protected function render($data = []) {
		$data = $this->process_data($data);
		if (!is_array($data)) {
			$data = ['content' => $data];
		}
		$keys = array_keys($data);
		$template = $this->template;
		foreach ($keys as $key) {
			$template = str_replace("{{" . $key . "}}", $data[$key], $template);
		}
		return $template;
	}

	protected function components() {
		return [];
	}

	protected function process_data($data) {
		return $data;
	}


}
