(function($) {

  $.fn.videoEmbedPrivacy = function() {
    $(this)
		.empty()
		.append($('<div class="video-wrapped-play">').html($(this).attr('data-embed-play')))
		.click(function(e) { if (e.target.tagName.toLowerCase() !== 'a') {
			$(this).html($(this).attr('data-embed-frame').replace(/(\/embed\/[^"]*\\?[^"]*)/, '$1&autoplay=1')).addClass('video-wrapped-clicked')
		}} )
    return this;
  };

  $('.video-wrapped').videoEmbedPrivacy();

}(jQuery));
