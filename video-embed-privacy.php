<?php
/*
 * Plugin Name: Embed videos and respect privacy
 * Plugin URI: https://wordpress.org/plugins/video-embed-privacy/
 * Description: Allows you to embed youtube videos without sending data to google on every page view.
 * Version: 2.0
 * Author: Michael Zangl
 * Text Domain: video-embed-privacy
 * Domain Path: /languages
 * License: GPL2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /languages
 * Text Domain: video-embed-privacy
 *
 * This plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * This plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this plugin. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
 */
defined('ABSPATH') or die('No script kiddies please!');

function video_embed_privacy_defaults() {
	$link = '<a href="' . __('https://www.google.com/intl/en/policies/privacy/', 'video-embed-privacy') . '" target="_blank">'
			. __('privacy policies of google', 'video-embed-privacy') . '</a>';
	return [
			'play' => __('Play', 'video-embed-privacy'),
			'yt_hint' => sprintf(__('This video will be embedded from Youtube. The %s apply.', 'video-embed-privacy'), $link),
			'cache' => 'false',
			'key' => ''
	];
}
function video_embed_privacy_option($name) {
	$defaults = video_embed_privacy_defaults();
	
	if (!isset($defaults[$name])) {
		die("Unknown option: $name");
	}

	return get_option("video-embed-privacy-$name", $defaults[$name]);
}
function video_embed_privacy_option_ne($name) {
	return video_embed_privacy_option($name) ?: video_embed_privacy_defaults()[$name];
}

function video_embed_privacy_translate($text, $url, $atts) {
	wp_enqueue_script('video-embed-privacy');
	$NO_JS_TEXT = esc_html__('Please activate JavaScript to view this video.', 'video-embed-privacy') . '<br/>' . esc_html__('Video-Link', 'video-embed-privacy') . ': <a href="' . htmlspecialchars($url) . '">' . $url . '</a>';
	
	$PLAY_TEXT = video_embed_privacy_option_ne('play') . '<div class="small">' . video_embed_privacy_option_ne('yt_hint') . '</div>';
	
	if (!preg_match("=youtube.*embed/([\\w-]+)=i", $text, $matches)) {
		return $text;
	}
	$v = $matches [1];
	
	$w = $atts ['width'];
	if (preg_match("/width=\"(\\d+)/", $text, $matches)) {
		$w = $matches [1] * 1;
	}
	
	$h = $atts ['height'];
	if (preg_match("/height=\"(\\d+)/", $text, $matches)) {
		$h = $matches [1] * 1;
	}
	
	$text = preg_replace('~https?\://www\.youtube\.com~', 'https://www.youtube-nocookie.com', $text);
	
	// plugin_dir_path( __FILE__ )
	$s = hash('sha256', video_embed_privacy_option('key') . $v);
	$preview = plugins_url("preview/$v.jpg?s=$s", __FILE__);
	return '<div class="video-wrapped" style="width: ' . $w . 'px; height: ' . $h . 'px; background-image: url(\'' . $preview . '\')" data-embed-frame="' . htmlspecialchars($text) . '" data-embed-play="' . htmlspecialchars($PLAY_TEXT) . '"><div class="video-wrapped-nojs">' . $NO_JS_TEXT . '</div></div>';
}

function video_embed_privacy_styles() {
	wp_register_style('video-embed-privacy', plugins_url('video-embed-privacy.css', __FILE__));
	wp_register_script('video-embed-privacy', plugins_url('video-embed-privacy.js', __FILE__), array(), '1.0', true);
	wp_enqueue_style('video-embed-privacy');
}

function video_embed_privacy_settings() {
	register_setting('video-embed-privacy', 'notice');
}

function video_embed_privacy_write_settings() {
	update_option('video-embed-privacy-key', wp_generate_password(48));
	
	$settings = [
			'cache' => video_embed_privacy_option('cache') === 'true',
			'key' => video_embed_privacy_option('key')
	];
	$file = dirname(__FILE__) . '/preview/settings.php';
	file_put_contents($file, "<?php if(!defined('__ACCESS_VEP_SETTINGS__')) die('Illegal access.'); return " . var_export($settings, true) . ';');
	
}

function video_embed_privacy_add_action_links ( $links ) {
	$mylinks = array(
			'<a href="' . admin_url( 'options.php?page=video-embed-privacy' ) . '">' . esc_html__('Settings', 'video-embed-privacy') . '</a>',
	);
	return array_merge( $links, $mylinks );
}

function video_embed_privacy_load_textdomain() {
	load_plugin_textdomain('video-embed-privacy', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

add_filter('embed_oembed_html', 'video_embed_privacy_translate', 11, 3);
add_action('wp_enqueue_scripts', 'video_embed_privacy_styles');
add_action('plugins_loaded', 'video_embed_privacy_load_textdomain');
register_activation_hook(__FILE__, 'video_embed_privacy_write_settings');

if (is_admin()) {
	add_action('admin_init', 'video_embed_privacy_settings');
	add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'video_embed_privacy_add_action_links');
	include dirname(__FILE__) . '/options.php';
}


if (is_dir(dirname(__FILE__) . '/plugin-update-checker')) {
	// Download updates from github for github snapshots
	require dirname(__FILE__) . '/plugin-update-checker/plugin-update-checker.php';
	$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
			'https://github.com/michaelzangl/wp-video-embed-privacy/',
			__FILE__,
			'video-embed-privacy'
	);
}
