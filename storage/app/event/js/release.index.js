/* 发布页JS */
;(function (window, $, undefined) {
	'use strict';
	/* 选择器 */
	var button = $('button#J-button');

	/* 封装编辑器内容获取方法 */
	var getEditorContent = function () {
		return EditorList.event.getContent().replace(/\_src\=\".*?\"/g, '');
	};

	/* 封装请求参数对象 */
	var args = {
		data: {},
		set: function(name, value) {
			this.data[name] = value;
			return this;
		},
		get: function () {
			return this.data;
		}
	};

	/* 异步上传图片 */
	$('#J-file-upload').on('change', function (event) {
		var $this = $(this);
		$.getScript($this.data('js'), function() {
			$.ajaxFileUpload({
				url: $this.data('url'),
				fileElementId: $this.attr('id'),
				secureuri: false,
				dataType: 'json',
				type: 'post',
				error: function() {
					ui.error('提交错误，请刷新页面重新尝试！');
				},
				success: function(data) {
					if (data.status != 1) {
						ui.error(data.meg);
						return false;
					};
					$($this.data('input')).val(data.data.attach_id);
					$($this.data('preview')).attr('src', $this.data('preview-url') + data.data.save_path + data.data.save_name).show();
					ui.success('上传成功');
				}
			});
		});
	});

	/* 地区选择 */
	(function() {
		var uri = $('#J-area').data('uri'),
			area = $('#J-area-area'),
			city = $(area.data('item')),
			temp = '<option value="%d">%s</option>';
		/* 数据请求封装 */
		var Httper = function(url, pid, callback) {
			$.post(url, {pid: pid}, callback, 'json');
		};
		/* 地区选择 */
		area.on('change', function(event) {
			var $this = $(this);
			Httper(uri, $this.val(), function(data) {
				var html = '<option value="0" checked>请选择</option>';
				for (var i in data) {
					html += temp.replace('%s', data[i].title).replace('%d', data[i].area_id);
				};
				city.html(html);
			});
		});
		/* 初始化地区数据 */
		Httper(uri, 0, function(data) {
			var html = '';
			for (var i in data) {
				html += temp.replace('%s', data[i].title).replace('%d', data[i].area_id);
			};
			area.html(html);
			area.change();
		});
	})();

	/* 获取页面中所有文本域，表单，选择器的值 */
	var getArgs = function () {
		var inp = $('#J-input input,#J-input select,').toArray();
		var sel;
		for (var i in inp) {
			sel = $(inp[i]);
			args.set(sel.attr('name'), sel.val());
		};
		args.set('content', getEditorContent()).set('audit', $('#J-audit:checked').val());
		return args.get();
	};

	/* 点击事件 */
	button.on('click', function (event) {
		event.preventDefault();
		// args.set('haha', 1).set('demo', 2);
		// console.log(getArgs());
		var $this = $(this);
		$.post($this.data('uri'), getArgs(), function(data) {
			if (data.status != 1) {
				ui.error(data.message);
				return false;
			};
			ui.success(data.message);
			setTimeout(function() {
				window.location.href = data.data;
			}, 2000);
		}, 'json');
	});

})(window, jQuery || $);