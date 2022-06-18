<?php
/**
 * Functions to register client-side assets (scripts and stylesheets) for the
 * Gutenberg block.
 *
 * @package fitet-monitor
 */

/**
 * Registers all block assets so that they can be enqueued through Gutenberg in
 * the corresponding context.
 *
 * @see https://wordpress.org/gutenberg/handbook/designers-developers/developers/tutorials/block-tutorial/applying-styles-with-stylesheets/
 */
function sample_block_block_init() {
	// Skip block registration if Gutenberg is not enabled/merged.
	if (!function_exists('register_block_type')) {
		return;
	}
	$dir = dirname(__FILE__);

	$index_js = 'sample-block/index.js';
	wp_register_script('sample-block-block-editor', plugins_url($index_js, __FILE__), ['wp-blocks', 'wp-i18n', 'wp-element',], filemtime("{$dir}/{$index_js}")
	);

	$editor_css = 'sample-block/editor.css';
	wp_register_style('sample-block-block-editor', plugins_url($editor_css, __FILE__), [], filemtime("{$dir}/{$editor_css}")
	);

	$style_css = 'sample-block/style.css';
	wp_register_style('sample-block-block', plugins_url($style_css, __FILE__), [], filemtime("{$dir}/{$style_css}")
	);

	register_block_type('fitet-monitor/sample-block', [
		'editor_script' => 'sample-block-block-editor',
		'editor_style' => 'sample-block-block-editor',
		'style' => 'sample-block-block',
	]);
}

add_action('init', 'sample_block_block_init');
