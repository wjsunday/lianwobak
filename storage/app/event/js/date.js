/* 日历 */
(function() {
	/* 日历点击 */
	var dateBody = $('#J-date-body');
	dateBody.on('click', '#J-time', function(event) {
		event.preventDefault();
		var d = $(this).data('date');
		var url = dateBody.data('url');
		url = url.replace('%s', d);
		window.location.href = url;
	});

	/* 封装日历渲染 */
	var dateFunc = function(dateObj) {
		var y = dateObj.getFullYear(); // 年
		var m = dateObj.getMonth(); // 月
		var l = new Array(); //初始化一个月日起列表

		var time = y + '-' + (m + 1) + '-' + '01 00:00:00';
		$.post($('#J-date-title').data('uri'), {time: time}, function(data) {

			/* 容错处理 */
			if (!data.data) {
				data.data = new Array();
			};

			/* 计算当月天数 */
			for (var i = 1; i <= 31; i += 1) {
				l.push(i);
				var lo = new Date();
				lo.setFullYear(y, m, 1);
				lo.setHours(0, 0, 0, 0);
				lo.setDate(lo.getDate() + i);
				if (lo.getDate() == 1) {
					break;
				};
				lo = null;
			};

			/* 计算开头补充多少天 */
			// var ol = 35 - l.length;
			// var pz = new Date();
			// pz.setFullYear(y, m, 1);
			// var pz = pz.getDay();
			// /* 计算结尾补充天数 */
			// var nz = ol - pz;
			var pz = new Date();
			pz.setFullYear(y, m, 1);
			pz.setHours(0, 0, 0, 0);
			pz = pz.getDay();
			var nz = new Date();
			nz.setFullYear(y, m, l.length);
			nz.setHours(0, 0, 0, 0);
			nz = nz.getDay();
			nz = 6 - nz;

			// /* 补充上一个月需要展示的天数 */
			for (; pz > 0; pz -= 1) {
				l.unshift('');
			};
			/* 补充下一个月占位天数 */
			for (; nz > 0; nz -= 1) {
				l.push('');
			};

			/* 取得本月第一天是星期几 */
			var z = new Date();
			z.setFullYear(y, m, 1);
			z.setHours(0, 0, 0, 0);
			z = z.getDay();

			/* 创建日历html */
			var html = '';
			var num  = 0;
			for (var i in l) {
				if (num == 0) {
					html += '<tr>';
				};
				if (l[i]) {
					var iHtml = '';
					if (typeof(data.data[l[i]]) !== 'undefined') {
						iHtml = '<i style="width: 6px;height: 6px;position: absolute;top: 2px;right: 2px;background: red;border-radius: 10px;"></i>';
					};
					html += '<td class="current-month weekday" id="J-time" style="position: relative;" data-date="' + y + '-' + (m + 1) + '-' + l[i] + '">' + l[i] + iHtml + '</td>';
					iHtml = null;
				} else {
					html += '<td class="current-month weekday">' + l[i] + '</td>';
				};
				if (num == 6) {
					html += '</tr>';
				};
				num += 1;
				if (num > 6) {
					num = 0;
				};
			};

			dateBody.html(html);
			$('#J-date-title').text((m + 1) + '月 ' + y);
			
		}, 'json');
	};

	var dateObj = new Date();

	/* 初始化搜索的时间 */
	var t = $('#J-date-title').data('time');
	t = parseInt(t + '000');
	// console.log(t);
	if (t > 0) {
		dateObj.setTime(t);
		// console.log(t);
	};

	var y = dateObj.getFullYear();
	var m = dateObj.getMonth();

	/* 初始化为当前 */
	dateFunc(dateObj);

	/* 上一月 */
	$('#J-date-prev').on('click', function(event) {
		event.preventDefault();
		if (m == 1) {
			m  = 12;
			y -= 1;
		} else {
			m -= 1;
		};
		dateObj.setFullYear(y, m, 1);
		dateObj.setHours(0, 0, 0, 0);
		dateFunc(dateObj);
		return false;
	});
	/* 下一月 */
	$('#J-date-next').on('click', function(event) {
		event.preventDefault();
		if (m == 12) {
			m  = 1;
			y += 1;
		} else {
			m += 1;
		};
		dateObj.setFullYear(y, m, 1);
		dateObj.setHours(0, 0, 0, 0);
		dateFunc(dateObj);
	});
})();