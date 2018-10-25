/* 首页JS */
(function(window, $, undefined) {
	'use strict';

	/* 搜索 */
	$('#J-search').on('click', function(event) {
		event.preventDefault();
		var $this = $(this),
			wd    = $($this.data('wd')).val(),
			url   = $this.data('url');
		url = url.replace('%s', wd);
		window.location.href = url;
	});

})(window, jQuery || $);