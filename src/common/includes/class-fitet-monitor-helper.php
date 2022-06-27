<?php

class Fitet_Monitor_Helper {

	public static function enqueue_style($handle, $src, $deps, $version, $media) {
		$minified = plugin_dir_path($src) . basename($src, '.css') . '.min.css';
		if (file_exists($minified)) {
			$url = plugin_dir_url($src) . basename($minified);
			wp_enqueue_style($handle, $url, $deps, $version, $media);
		} else if (file_exists($src)) {
			$url = plugin_dir_url($src) . basename($src);
			wp_enqueue_style($handle, $url, $deps, $version, $media);
		}

	}

	public static function enqueue_script($handle, $src, $deps, $version, $in_footer) {
		$minified = plugin_dir_path($src) . basename($src, '.js') . '.min.js';
		if (file_exists($minified)) {
			$url = plugin_dir_url($src) . basename($minified);
			wp_enqueue_script($handle, $url, $deps, $version, $in_footer);
		} else if (file_exists($src)) {
			$url = plugin_dir_url($src) . basename($src);
			wp_enqueue_script($handle, $url, $deps, $version, $in_footer);
		}
	}
}
