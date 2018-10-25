; '后台分类操作JS';
(function (window, $, undefined) {
    '程序主体';

    /* 添加按钮 */
    $('#subject-add').on('click', function(event) {
        event.preventDefault();
        window.location.href = $(this).data('url');
    });

    /* 搜索按钮 */
    $('#subject-search').on('click', function(event) {
        event.preventDefault();
        admin.fold('search_form');
    });

    /* # 批量删除 */
    $('#subject-delete').on('click', function(event) {
        event.preventDefault();
        var ids = admin.getChecked();
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
