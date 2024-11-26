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
	$ytLink = '<a href="' . __('https://www.google.com/intl/en/policies/privacy/', 'video-embed-privacy') . '" target="_blank">'
			. __('privacy policies of google', 'video-embed-privacy') . '</a>';
	$vimeoLink = '<a href="' . __('https://vimeo.com/privacy', 'video-embed-privacy') . '" target="_blank">'
			. __('privacy policies of vimeo', 'video-embed-privacy') . '</a>';
	return [
			'show' => __('Show Content', 'video-embed-privacy'),
			'yt_show' => __('Play Video', 'video-embed-privacy'),
			'vimeo_show' => __('Play Video', 'video-embed-privacy'),
			'generic_hint' => __('This content is referring to %s and will be loaded from an external source.', 'video-embed-privacy'),
			'yt_hint' => sprintf(__('This video will be embedded from Youtube. The %s apply.', 'video-embed-privacy'), $ytLink),
			'vimeo_hint' => sprintf(__('This video will be embedded from Vimeo. The %s apply.', 'video-embed-privacy'), $vimeoLink),
			'cache' => 'false',
			'replace_unknown' => 'true',
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

function video_embed_privacy_available() {
	return [
		'yt' => [
			'name' => __('Youtube', 'video-embed-privacy'),
			'videoIdMatch' => "=youtube.*embed/([\\w-]+)=i",
			'textFixer' => function($in) {
				return preg_replace('~https?\://www\.youtube\.com~', 'https://www.youtube-nocookie.com', $in);
			}
		],
		'vimeo' => [
			'name' => __('Vimeo', 'video-embed-privacy'),
			'videoIdMatch' => '=://player.vimeo.com/video/(\\d+)=i',
		]
	];
}

function video_embed_privacy_translate($text, $url, $atts) {
	$noJsText = esc_html__('Please activate JavaScript to view this video.', 'video-embed-privacy') . '<br/>' . esc_html__('Video link', 'video-embed-privacy') . ': <a href="' . htmlspecialchars($url) . '">' . $url . '</a>';
	
	$playText = '<span>' . video_embed_privacy_option_ne('show') . '</span><div class="small"><span>' . sprintf(video_embed_privacy_option_ne('generic_hint'), preg_replace("~\\w+://(.*?)/.*~", "$1", $url)) . '</span></div>';
	$embedText = $text;

	$w = $atts ['width'];
	if (preg_match("/width=\"(\\d+)/", $text, $widthMatches)) {
		$w = $widthMatches [1] * 1;
	}
	
	$h = $atts ['height'];
	if (preg_match("/height=\"(\\d+)/", $text, $heightMatches)) {
		$h = $heightMatches [1] * 1;
	}
	
	$style = 'width: ' . $w . 'px; min-height: ' . $h . 'px;';
	$class = 'video-wrapped';
	$doReplacement = video_embed_privacy_option('replace_unknown') === 'true';

	$supported = video_embed_privacy_available();

	foreach ($supported as $id => $settings) {
		if (preg_match($settings['videoIdMatch'], $text, $matches)) {
			$playText = '<span>' . video_embed_privacy_option_ne($id . '_show') . '</span><div class="small"><span>' . video_embed_privacy_option_ne($id . '_hint') . '</span></div>';
			$v = $matches [1];
		
			if (isset($settings['textFixer'])) {
				$embedText = $settings['textFixer']($embedText);
			}

			$s = hash('sha256', video_embed_privacy_option('key') . $id . '/' . $v);
			$preview = plugins_url("preview/$id/$v.jpg?s=$s", __FILE__);
			$class .= ' video-wrapped-video video-wrapped-' . $id;
			$style .= ' background-image: url(\'' . $preview . '\')';
			$doReplacement = true;
			break;
		}
	}
	
	if ($doReplacement) {
		return '<div class="' . $class . '" style="' . $style . '" data-embed-frame="' . htmlspecialchars($embedText) . '" data-embed-play="' . htmlspecialchars($playText) . '"><div class="video-wrapped-nojs"><span>' . $noJsText . '</span></div></div>';
	} else {
		return $text;
	}
}

function video_embed_privacy_styles() {
	wp_register_style('video-embed-privacy', plugins_url('video-embed-privacy.css', __FILE__));
	wp_register_script('video-embed-privacy', plugins_url('video-embed-privacy.js', __FILE__), array(), '1.0', true);
	wp_enqueue_style('video-embed-privacy');
	wp_enqueue_script('video-embed-privacy');
}

function video_embed_privacy_settings() {
	add_editor_style(plugins_url('video-embed-privacy.css', __FILE__));
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
	// Download updates from github for github snapshots - this code will only execute if the gradle script is used for building.
	require dirname(__FILE__) . '/plugin-update-checker/plugin-update-checker.php';
	$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
			'https://github.com/michaelzangl/wp-video-embed-privacy/',
			__FILE__,
			'video-embed-privacy'
	);
}
