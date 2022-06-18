<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-helper.php';

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
		$file = plugin_dir_path($file) . basename($file, '.php') . '.css';
		Fitet_Monitor_Helper::enqueue_style(get_class($this), $file, [], $this->version, 'all');
	}

	public function enqueue_scripts() {
		$file = (new ReflectionClass($this))->getFileName();
		$file = plugin_dir_path($file) . basename($file, '.php') . '.js';
		Fitet_Monitor_Helper::enqueue_script(get_class($this), $file, ['jquery'], $this->version, false);
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

	public function initialize() {
		$this->load_components();
		$this->load_template();
		$this->enqueue_scripts();
		$this->enqueue_styles();
		foreach ($this->components as $component) {
			$component->initialize();
		}
	}

	protected final function render($data = []) {
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
