/* 活动详情页面JS */
;(function(window, $, undefined) {
	'use strict';

	/* 地图 */
	$('#J-map').on('click', function(event) {
		event.preventDefault();
		var $this = $(this);
		/* 遮罩 */
		var fixd = $('<div style="background-color: #000;"></div>');
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
		var mapSrc = 'http://api.map.baidu.com/geocoder?address=%s&output=html&src=%name';
		mapSrc     = mapSrc.replace('%s', $this.data('address'));
		mapSrc     = mapSrc.replace('%name', $this.data('name'));
		/* 地图弹窗 */
		var map = $('<div class="hd-detailmap">\
			<div class="hd-detailmapbox">\
				<iframe style="width: 100%; height: 100%;border: none;" scrolling="auto" seamless="seamless" src="' + mapSrc + '"></iframe>\
			</div>\
			<div class="hd-fixeclose" id="J-close"></div>\
		</div>');
		$('body').append(map);
		var w = map.width() / 2;
		var h = map.height() / 2;
		map.css({
			position: 'fixed',
			zIndex: 999,
			left: '50%',
			top: '50%',
			marginTop: -h,
			marginLeft: -w
		});
		/* 地图关闭 */
		map.on('click', '#J-close', function(event) {
			event.preventDefault();
			map.remove();
			fixd.remove();
		});
	});

	/* 关注活动/取消关注 */
	$('#J-star').on('click', function(event) {
		event.preventDefault();
		var $this = $(this);
		/* 关注 */
		if ($this.data('star') == 0) {
			$.post($this.data('star-url'), {eid: $this.data('eid')}, function(data) {
				if (data.status == 0) {
					ui.error(data.message);
					return false;
				};
				ui.success(data.message);
				$this.removeClass('jia').addClass('jian').html('<i></i>取消关注').data('star', 1);
			}, 'json');
			return false;
		};
		$.post($this.data('star-un'), {eid: $this.data('eid')}, function(data) {
			if (data.status == 0) {
				ui.error(data.message);
				return false;
			};
			ui.success(data.message);
			$this.removeClass('jian').addClass('jia').html('<i></i>关注').data('star', 0);
		}, 'json');
		return false;
	});

	/* 报名 */
	var enrollment = $('#J-enrollment');
	enrollment.on('click', function(event) {
		event.preventDefault();
		var $this = $(this);
		if ($this.data('show') == 'on') {
			ui.error('该活动暂时不可报名');
			return false;
		} else if ($this.data('status') == 0) {
			var html = '<div class="hd-fixdinfo">\
				<h2>活动报名</h2>\
				<div class="hd-fixdtable">\
					<dl>\
						<dt>\
							<label><span class="hd-txt_impt">*</span>称呼：</label>\
						</dt>\
						<dd>\
							<input class="hd-cjtext" id="J-enrollment-name" hd-fixdinfol" type="text" placeholder="请输入你的称呼">\
							<label class="hd-fixerlabel">性别：</label>\
							<input class="hd-fixeradio" id="J-enrollment-sex" name="sex" type="radio" checked value="1">\
							<label class="hd-fixeradio">男</label>\
							<input class="hd-fixeradio" id="J-enrollment-sex" name="sex" type="radio" value="0">\
							<label class="hd-fixeradio">女</label>\
						</dd>\
					</dl>\
					<dl>\
						<dt><label><span class="hd-txt_impt">*</span>联系方式：</label></dt>\
						<dd>\
							<input class="hd-cjtext hd-fixdinfoxl" id="J-enrollment-phone" type="text" placeholder="请输入您的联系方式">\
						</dd>\
					</dl>\
					<!-- <dl>\
						<dt><label><span class="hd-txt_impt">*</span>报名人数：</label></dt>\
						<dd>\
							<input class="hd-cjtext hd-fixdinfoxl" id="J-enrollment-num" type="number" placeholder="请输入你需要报名的人数，个人报名默认即可" min="1" sleep="1" value="1">\
						</dd>\
					</dl> -->\
					<dl>\
						<dt><label>备注：</label></dt>\
						<dd>\
							<textarea class="hd-cjtext hd-fixdbz" id="J-enrollment-note" placeholder="备注内容"></textarea>\
						</dd>\
					</dl>\
					<dl>\
						<dt><label>&nbsp;</label></dt>\
						<dd>\
							<p class="hd-fixdp">报名活动后，发起人会及时与你取得联系为你安排活动事项。</p>\
							<button class="hd-fixdsubmit" id="J-enrollment-submit" type="submit">报名</button>\
						</dd>\
					</dl>\
				</div>\
				<div class="hd-fixeclose" id="J-enrollment-close"></div>\
				<div></div>\
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
			var fixd = $('<div style="background-color: #000;"></div>');
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

			/* 关闭事件 */
			html.on('click', '#J-enrollment-close', function(event) {
				event.preventDefault();
				fixd.remove();
				html.remove();
			});

			/* 表单提交事件 */
			html.on('click', '#J-enrollment-submit', function(event) {
				event.preventDefault();
				var args = {
					name: html.find('#J-enrollment-name').val(),
					sex: html.find('#J-enrollment-sex:checked').val(),
					phone: html.find('#J-enrollment-phone').val(),
					note: html.find('#J-enrollment-note').val(),
					// num: html.find('#J-enrollment-num').val(),
					eid: $this.data('eid')
				};
				$.post($this.data('enrollment'), args, function(data) {
					if (data.status != 1) {
						ui.error(data.message);
						return false
					};
					html.find('#J-enrollment-close').click();
					ui.success(data.message);
					setTimeout(function() {
						window.location.href = window.location.href;
					}, 2000);
				}, 'json');
			});
			return false;
		};
		/* 取消报名 */
		$.post($this.data('unenrollment'), {eid: $this.data('eid')}, function(data) {
			if (data.status != 1) {
				ui.error(data.message);
				return false;
			};
			ui.success(data.message);
			$this.html('<i></i>报名').removeClass('jian').addClass('jia').data('status', 0);
			return false;
		}, 'json');
		return false;
	});

	/* 管理 */
	$('#J-admin').on('click', function(event) {
		event.preventDefault();
		var $this = $(this),
			show  = $this.data('show');
		if (show == 'on') {
			$($this.data('item')).show();
			$this.data('show', 'off');
			return false;
		};
		$($this.data('item')).hide();
		$this.data('show', 'on');
	});

	/* 费用提醒 */
	$('#J-price-tips').on({
		mouseover: function() {
			$(this).data('empty') == '0' && $($(this).data('item')).show()
		},
		mouseout: function() {
			$($(this).data('item')).hide();
		}
	});

})(window, jQuery || $);