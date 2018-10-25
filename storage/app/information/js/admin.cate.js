; '后台分类操作JS';
(function (window, $, undefined) {
    '程序主体';

    /* 搜索分类 */
    $('#information-search').on('click', function (event) {
        event.preventDefault();
        admin.fold('search_form');
    });

    /* 添加分类跳转 */
    $('#information-add').on('click', function (event) {
        event.preventDefault();
        window.location.href = $(this).data('url');
    });

    /* # 保存排序等级 */
    $('#information-submit').on('click', function (event) {
        event.preventDefault();
        var ranks = $(document).find('input#information-ranks').toArray();
        var data  = '';
        for(var i in ranks) {
            ranks[i] = $(ranks[i])
            data += ranks[i].data('id') + '=' + ranks[i].val();
            if (i < ranks.length - 1) {
                data += ',';
            };
        }
        $.post($(this).data('uri'), {'ranks': data}, function (data) {
            data && setTimeout(function() {
                window.location.href = window.location.href;
            }, 1500);
            ui.success('提交成功');
        });
    });

    /* # 批量删除分类 */
    $('#information-delete').on('click', function (event) {
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
