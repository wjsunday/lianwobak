/* 分类管理js */
; (function (window, $, undefind) {
	'use strict';

	/* 添加分跳转操作 */
	$('#add').on('click', function(event) {
		event.preventDefault();
		window.location.href = $(this).data('uri');
	});

	/* 删除操作 */
	$('#delete').on('click', function (event) {
		event.preventDefault();
		var ids = admin.getChecked();
		// console.log(ids);return ;
        var str = '';
        for (var i in ids) {
            str += ids[i];
            if (ids.length - 1 > i) {
                str += ',';
            };
        };
        var url = $(this).data('uri');
        url = url.replace(/\_\_IDS\_\_/g, str)
        window.location.href = url;
	});

})(window, jQuery || $);