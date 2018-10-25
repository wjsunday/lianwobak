/**
 * 投稿JS
 */
;(function (window, $, undefined) {
  'use strict';

  /* 各个控件列表 */
  var subject  = $('#subject-title'),
      cate     = $('#subject-cate'),
      abstract = $('#subject-abstract'),
      content  = EditorList.editor,
      button   = $('#subject-submit');

  /* # 点击提交 */
  button.on('click', function(event) {
    event.preventDefault();
    var $this = $(this);
    var url   = $this.data('uri');
    var args  = {
      'subject' : subject.val(),
      'abstract': abstract.val(),
      'cid'     : cate.find('.current').data('cid'),
      'content' : content.getContent()
    };
    $.post(url, args, function(data) {
      /*optional stuff to do after success */
      if (data.status == 1) {
        ui.success(data.info);
        setTimeout(function() {
          window.location.href = data.data.jumpUrl;
        }, 1500);
      } else {
        ui.error(data.info);
      };
    }, 'json');
  });

  /* 分类选择 */
  cate.on('click', 'span.span', function(event) {
    event.preventDefault();
    /* Act on the event */
    cate.find('span.span').removeClass('current');
    $(this).addClass('current');
  });

})(window, jQuery || $);