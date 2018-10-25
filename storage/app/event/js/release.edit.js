/* 发布页JS */
;(function (window, $, undefined) {
	'use strict';
	/* 选择器 */
	var button = $('button#J-button');

	/* 封装编辑器内容获取方法 */
	var getEditorContent = function () {
		return EditorList.event.getContent().replace(/\_src\=\".*?\"/g, '');
	};

	button.on('click', function(event) {
		event.preventDefault();
		var $this = $(this);
		$.post($this.data('uri'), {
			eid: $this.data('eid'),
			content: getEditorContent()
		}, function(data) {
			if (data.status == 0) {
				ui.error(data.message);
				return false;
			};
			ui.success(data.message);
			setTimeout(function() {
				window.location.href = $this.data('jump');
			}, 1500);
		}, 'json');
	});

})(window, jQuery || $);