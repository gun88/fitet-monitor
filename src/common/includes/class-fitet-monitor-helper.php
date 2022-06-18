<?php

class Fitet_Monitor_Helper {

	public static function enqueue_style($handle, $src, $deps, $version, $media) {
		$minified = plugin_dir_path($src) . basename($src, '.css') . '.min.css';
		$file_name = basename(file_exists($minified) ? $minified : $src);
		$url = plugin_dir_url($src) . $file_name;
		wp_enqueue_style($handle, $url, $deps, $version, $media);
	}

	public static function enqueue_script($handle, $src, $deps, $version, $in_footer) {
		$minified = plugin_dir_path($src) . basename($src, '.js') . '.min.js';
		$file_name = basename(file_exists($minified) ? $minified : $src);
		$url = plugin_dir_url($src) . $file_name;
		wp_enqueue_script($handle, $url, $deps, $version, $in_footer);
	}
}
