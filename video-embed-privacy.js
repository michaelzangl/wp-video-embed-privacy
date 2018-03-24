
jQuery(function() {
	jQuery(".video-wrapped").each(function() {
		jQuery(this)
		.empty()
		.append(jQuery('<div class="video-wrapped-play">').html(jQuery(this).attr('data-embed-play')))
		.click(function(e) { if (e.target.tagName.toLowerCase() !== 'a') {
			jQuery(this).html(jQuery(this).attr('data-embed-frame').replace(/(\/embed\/[^"]*\\?[^"]*)/, '$1&autoplay=1'))
		}} )
	})
});
