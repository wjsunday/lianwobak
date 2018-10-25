/* 右侧用的JS */
;(function(window, $, undefined) {
	'use strict';

	/* 推荐活动刷新 */
	$('#J-event-top').on('click', function(event) {
		event.preventDefault();
		var html = '';
		var $this = $(this);
		$.get($this.data('url'), function(data) {
			$('#J-event-top-body').html(data);
		}, 'html');
	});

})(window, jQuery || $);