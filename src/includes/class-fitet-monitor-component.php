<?php

abstract class Fitet_Monitor_Component {

	protected $components = [];

	protected $version;

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

	public final function initialize() {
		$this->load_components();
		$this->enqueue_scripts();
		$this->enqueue_styles();
		foreach ($this->components as $component) {
			$component->enqueue_scripts();
			$component->enqueue_styles();
			$component->load_components();
		}

	}

	public function enqueue_style($file) {
		if (file_exists(plugin_dir_path($file) . basename($file, '.php') . '.min.css')) {
			// use minified css if available
			$default_css_file_url = plugin_dir_url($file) . basename($file, '.php') . '.min.css';
			wp_enqueue_style(get_class($this), $default_css_file_url, array(), $this->version, 'all');
		} else if (file_exists(plugin_dir_path($file) . basename($file, '.php') . '.min.css')) {
			$default_css_file_url = plugin_dir_url($file) . basename($file, '.php') . '.css';
			wp_enqueue_style(get_class($this), $default_css_file_url, array(), $this->version, 'all');
		} else {
			wp_enqueue_style(get_class($this), $file, array(), $this->version, 'all');
		}
	}

	protected abstract function render($data = []);

	protected function components() {
		return [];
	}


}
