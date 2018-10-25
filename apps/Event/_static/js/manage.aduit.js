/* 审核管理 */
; (function (window, $, undefind) {
	'use strict';

	/* 驳回申请 */
	$('#J-aduit').on('click', '.J-aduit-no', function(event) {
		event.preventDefault();
		var url   = $('#J-aduit').data('uri');
		var $this = $(this);
		var fixd  = $('<div style="background-color: #000;"></div>');
		$('body').append(fixd);
		fixd.css({
			position: 'fixed',
			top: '0',
			right: '0',
			bottom: '0',
			left: '0',
			zIndex: 998,
			backgroundColor: 'rgba(0, 0, 0, .8)',
			opacity: '.8'
		});
		var html = '<div class="hd-fixdreject">\
			<textarea class="hd-cjtext hd-fixdbz" id="J-content" style="height: 68px;padding: 10px;" placeholder="驳回理由"></textarea>\
			<button class="hd-fixdsubmit" id="J-submit" type="submit">驳回申请</button>\
			<div class="hd-fixeclose" id="J-close"></div>\
		</div>';
		html = $(html);
		$('body').append(html);
		var w = html.width() / 2;
		var h = html.height() / 2;
		html.css({
			position: 'fixed',
			zIndex: 999,
			left: '50%',
			top: '50%',
			marginTop: -h,
			marginLeft: -w
		});
		/* 关闭事件 */
		html.on('click', '#J-close', function(event) {
			event.preventDefault();
			fixd.remove();
			html.remove();
		});
		/* 提交 */
		html.on('click', '#J-submit', function(event) {
			event.preventDefault();
			$.post(url, {
				eid: $this.data('eid'),
				uid: $this.data('uid'),
				content: html.find('#J-content').val()
			}, function(data) {
				if (data.status == 0) {
					ui.error(data.message);
					return false;
				};
				html.find('#J-close').click();
				ui.success(data.message);
				setTimeout(function() {
					window.location.href = window.location.href;
				}, 1500);
			}, 'json');
			return false;
		});
		return false;
	});

})(window, jQuery || $);