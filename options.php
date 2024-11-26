<?php

function video_embed_privacy_load_settings_page() {
	if(isset($_GET['settings-updated']) && $_GET['settings-updated']) {
		video_embed_privacy_write_settings();
	}
}

function video_embed_privacy_settings_init() {
	register_setting('vepPage', 'video-embed-privacy-yt_show');
	register_setting('vepPage', 'video-embed-privacy-yt_hint');
	register_setting('vepPage', 'video-embed-privacy-vimeo_show');
	register_setting('vepPage', 'video-embed-privacy-vimeo_hint');
	register_setting('vepPage', 'video-embed-privacy-cache');
	register_setting('vepPage', 'video-embed-privacy-replace_unknown');
	
	add_settings_section('video_embed_privacy_vepPage_section', __('General', 'video-embed-privacy'), 'video_embed_privacy_settings_section_callback', 'vepPage');
	add_settings_field('video_embed_privacy_replace_unknown', __('Replace all [embed]s', 'video-embed-privacy'), 'video_embed_privacy_relace_unknown_render', 'vepPage', 'video_embed_privacy_vepPage_section');
	add_settings_field('video_embed_privacy_cache', __('Cache images', 'video-embed-privacy'), 'video_embed_privacy_cache_render', 'vepPage', 'video_embed_privacy_vepPage_section');

	foreach (video_embed_privacy_available() as $id => $options) {
	add_settings_section('video_embed_privacy_vepPage_section_' . $id, $options['name'], 'video_embed_privacy_settings_section_xx_callback', 'vepPage');
	add_settings_field('video_embed_privacy_text_field_0', __('Play button text', 'video-embed-privacy'), 'video_embed_privacy_text_xx_render', 'vepPage', 'video_embed_privacy_vepPage_section_' . $id, $id . '_show');
	add_settings_field('video_embed_privacy_text_field_1', __('HTML below play button text', 'video-embed-privacy'), 'video_embed_privacy_text_xx_render', 'vepPage', 'video_embed_privacy_vepPage_section_' . $id, $id . '_hint');
	}
}

function video_embed_privacy_text_xx_render($id) {
	?>
<input type='text'
	name='video-embed-privacy-<?php echo $id ?>'
	placeholder="<?php echo esc_html(video_embed_privacy_defaults()[$id])?>"
	value="<?php echo htmlspecialchars(get_option('video-embed-privacy-' . $id, '')); ?>"
	style="width: 100%">
<?php
}

function video_embed_privacy_cache_render() {
	?>
<input type="checkbox"
	name="video-embed-privacy-cache"
	value="true"
	<?php 
	if (video_embed_privacy_option('cache') === 'true') {
		echo ' checked';
	}
	?>>
<?php
}

function video_embed_privacy_relace_unknown_render() {
	?>
<label><input type="checkbox"
	name="video-embed-privacy-replace_unknown"
	value="true"
	<?php 
	if (video_embed_privacy_option('replace_unknown') === 'true') {
		echo ' checked';
	}
	?>>
<?php
	echo __( 'Replace all unknown embed codes by a generic mask.', 'video-embed-privacy' );
?>
	</label>
<?php
}

function video_embed_privacy_settings_section_callback(  ) {
}

function video_embed_privacy_settings_section_xx_callback(  ) {
	echo __( 'You can enter your custom texts here. Leave empty to use defaults.', 'video-embed-privacy' );
}

function video_embed_privacy_show_options() {
	?>
<div class="wrap">
	<h1><?php echo _e('Video Embed Privacy settings', 'video-embed-privacy') ?></h1>

	<form method="post" action="options.php"> 
<?php
	settings_fields('vepPage');
	do_settings_sections('vepPage');
	submit_button();
	?>
</form>

</div>
<?php
}

function video_embed_privacy_admin_menu() {
	add_submenu_page('_doesnt_exist', __('Video Embed Privacy settings', 'video-embed-privacy'), '', 'manage_options', 'video-embed-privacy', 'video_embed_privacy_show_options');
}

add_action('admin_init', 'video_embed_privacy_settings_init');
add_action('admin_menu', 'video_embed_privacy_admin_menu');
add_filter('load-admin_page_video-embed-privacy', 'video_embed_privacy_load_settings_page');
