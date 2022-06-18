<?php


class Wordpress_Double {

}


function wp_enqueue_style($handle, $src, $deps, $ver, $media) {}

function plugin_dir_path($file) {
	return dirname($file) . '/';
}

function plugin_dir_url($file) {
	return "http://test-host.com/plugin";
}

function wp_enqueue_script($handle, $src, $deps, $ver, $in_footer) {}
