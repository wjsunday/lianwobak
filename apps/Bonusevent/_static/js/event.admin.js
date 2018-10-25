/* 活动管理 - admin */
; (function (window, $, undefind) {
	'use strict';

	/* 搜索 */
	$('#search').on('click', function (event) {
		event.preventDefault();
		admin.fold('search_form');
	});

	/* 多选删除 */
	$('#delete').on('click', function (event) {
		event.preventDefault();
		var ids = admin.getChecked(),
		    str = '';
		for (var i in ids) {
			str += ids[i];
			if (ids.length - 1 > i) {
				str += ',';
			};
		};
		var url = $(this).data('uri').replace(/\_\_IDS\_\_/g, str);
		window.location.href = url;
	});

})(window, jQuery || $);