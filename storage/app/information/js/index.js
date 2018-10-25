/**
 * 首页JS
 */
;(function (window, $, undefined) {
  'use strict';

  /* 首页的幻灯片 */
  $('.focus').slide({
    titCell: '#tip li',
    mainCell: '#pic ul',
    effect: 'left',
    autoPlay: true,
    delayTime: 200
  });

})(window, jQuery || $);