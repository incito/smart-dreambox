$(function() {
	// dialog close
	$('.dialog-box .title .close').click(function() {
		$(this).parents('.dialog').fadeOut();
	})
	// checkbox click
	$('.checkbox').click(function(event) {
		var val = $(this).find('input').val();
		event.stopPropagation();
		$(this).find('i').toggleClass('checked').toggleClass('check');
		$(this).find('input').val(val == 0 ? 1 : 0);
	});
	// select click
	$('.selectGroup')
		.click(
			function(event) {
				var _this = $(event.target);
				if (_this.is('.select') || _this.is('label') || _this.is('em')) {
					_this = $(this).find('.select');
					$('.selectGroup .select-items').not(_this.next()).hide();
					_this.next().toggle();
					_this.find('em').toggleClass('bottomdirection')
						.toggleClass('topdirection');
				} else if (_this.is('li')) {
					_this.parent().find('li.active').removeClass(
						'active');
					_this.addClass('active');
					_this.parents('.selectGroup').find('.select').attr(
						'date-value', _this.attr('date-value'))
						.find('.text').text(_this.text());
					_this.parent().hide();
					$(this).find('em').toggleClass('bottomdirection')
						.toggleClass('topdirection');
				}
			});
	var timer = null;
	var isShow = true;
	$(document).on("mouseenter", ".href", function(event) {
		$('div.name .p-dialog').remove();
		_this = $(event.target);
		isShow = true;
		timer = setTimeout(function() {
			loadCard(_this, isShow);
		}, 500);

	}).on("mouseleave", ".name", function(event) {
		clearTimeout(timer);
		isShow = false;
		$('div.name .p-dialog').remove();
	});
	$(document).on("click", ".href", function(event) {
		_this = $(event.target);
		window.location.href = _this.attr('href');
	});


	// 回到顶部
	;(function($){
		var indexNavActive = $('#header').find('.nav .active');
		// 窗口高度定为800像素
		var windowHeight = 800;
		// 标示元素是否显示
		var isShow = false;
		// 创建元素
		var backTop = $('<a class="backtop" title="返回顶部" href="javascript:;"></a>');
		// 首页
		if(indexNavActive.text() === '首页'){
			backTop.css({
				marginLeft:'250px'
			});
		}
		// 回到顶部功能实现
		backTop.on('click',function(){
			$(document).scrollTop(0);
		});
		// 滚动条高度计算,控制回到顶部是否显示
		$(document).on('scroll',function(){
			var scrollTop = $(document).scrollTop();
			// 滚动条高度超过一屏时才显示
			if(windowHeight < scrollTop){
				// 只有当元素是隐藏状态才执行动画
				if(!isShow){
					backTop.animate({
						opacity:1
					},150);
					isShow = true;
				}
			}else{
				// 只有当元素是显示状态才执行动画
				if(isShow){
					backTop.animate({
						opacity:0
					},150);
					isShow = false;
				}
			}
		});
		backTop.appendTo('body');
	})(jQuery);

	// html5 placeholder模拟
	$('.input-group input,#header-search-input,#aht-search-input,#map-search-input')
		// 获取焦点时，判断是提示文字是否显示，如果显示，则隐藏
		.on('focus', function() {
			var placeholder = $(this).parent().find('.placeholder');
			if (!placeholder.is(':hidden')) {
				placeholder.hide();
			}
		})
		// 失去焦点时，判断文本框是否为空，如果为空则重新显示提示文字
		.on('blur', function() {
			var placeholder = $(this).parent().find('.placeholder');
			var val = $(this).val();
			if (val === '') {
				placeholder.show();
			}
		});

	//收缩下拉框
	$(document).click(function(event) {
		var pa = $(event.target).closest('.selectGroup');
		if (!pa.size()) {
			$('.selectGroup .select-items').hide();
			$('.selectGroup .select >em').removeClass().addClass('bottomdirection');
		} else {
			$('.selectGroup').not(pa).find('.select-items').hide();
		}
	});

});

var dreambox = {
	timer: "",
	close: false
};
/**
 * alert
 */
dreambox.alert = function(info, callback, time) {
	var $div = $("<div class=\"overlayer\"></div><div class=\"dialog-box infoBox pr\" style=\"display: block;z-index: 999999;\"><h1 class=\"title\">提示</h1><div class=\"dialog-main\"><p>" + info + "</p><div class=\"row center\"><p></p></div></div><div class=\"dialog-footer\"><p><input class=\"btn footer-submit fr\" type=\"button\" value=\"确定\"/></p></div></div>");
	$('div.overlayer').css("z-index", '999999');
	$div.find('.btn.footer-submit.fr').click(function() {
		$div.remove();
		$('div.overlayer').css("z-index", '999');
		if (callback) {
			callback();
		}
	});
	$div.hide();
	$('body').append($div);
	eleCenter($('.dialog-box').last()); // 居中
	$div.fadeIn(400);
	if (time) {
		dreambox.timeout($div, time, callback);
	}
}

dreambox.dialog = function(title, info, callback, time) {
	var div = '<div class="dialog" style="left: 0px;display:none;"><div class="overlayer"></div><div class="dialog-box regsucc" style="position: fixed;"><h1 class="title">' + title + '<a href="javascript:;" class="close fr"><i class="icons close"></i></a></h1><div class="dialog-main"><div class="row center"><img src="' + THEME_URL + '/images/pic2.png"></div><div class="row center"><h4></h4><p></p></div></div></div></div>';
	var _div = $(div);
	_div.find('h4').text(info);
	$('body').append(_div);
	_div.fadeIn();
	eleCenter($('.dialog-box').last()); // 居中
	_div.find('.icons.close').click(function() {
		dreambox.close = true;
		_div.remove();
		if (dreambox.timer) {
			clearInterval(dreambox.timer);
		}
		if (callback) {
			callback();
		}
	})

	if (!time) {
		time = 2;
	}
	dreambox.timeout(_div, time, callback);
}

dreambox.timeout = function(_div, time, callback) {
	_div.find('.row.center p').text(time + '秒后自动消失');
	if (time == 0) {
		_div.remove();
		clearInterval(dreambox.timer);
		if (callback) {
			callback();
		}
		return;
	}
	timer = setTimeout(function() {
		dreambox.timeout(_div, time - 1, callback);
	}, 1000);
}
/**
 * confirm
 */
dreambox.confirm = function(info, confirmInfo, title, confirmCallback,
	cancelCallback) {
	var $div = $("<div class=\"overlayer\"></div><div class=\"dialog-box infoBox\" style=\"display: block;z-index: 999999;\"><h1 class=\"title\">" + title + "</h1><div class=\"dialog-main\"><p>" + info + "</p><br>" + (confirmInfo ? "<p>" + confirmInfo + "</p>" : "") + "</div><div class=\"dialog-footer\"><p><input class=\"btn footer-cancel\" type=\"button\" value=\"取消\" /><input class=\"btn footer-submit fr\" type=\"button\" value=\"确定\" /></p></div></div>");
	$('div.overlayer').css("z-index", '999999');
	$div.find('.btn.footer-cancel').click(function() {
		$div.remove();
		$('div.overlayer').css("z-index", '999');
		if (cancelCallback) {
			cancelCallback();
		}
	})
	$div.find('.btn.footer-submit.fr').click(function() {
		$div.remove();
		$('div.overlayer').css("z-index", '999');
		if (confirmCallback) {
			confirmCallback();
		}
	})
	$div.hide();
	$('body').append($div);
	eleCenter($('.dialog-box').last()); // 居中
	$div.fadeIn(400);
}

function Map() {
	this.container = new Object();
}

Map.prototype.put = function(key, value) {
	this.container[key] = value;
}

Map.prototype.get = function(key) {
	return this.container[key];
}

Map.prototype.size = function() {
	var count = 0;
	for (var key in this.container) {
		// 跳过object的extend函数
		if (key == 'extend') {
			continue;
		}
		count++;
	}
	return count;
}

Map.prototype.remove = function(key) {
	delete this.container[key];
}

Map.prototype.toString = function() {
	var str = "";
	for (var i = 0, keys = this.keySet(), len = keys.length; i < len; i++) {
		str = str + keys[i] + "=" + this.container[keys[i]] + ";\n";
	}
	return str;
}
/**
 * relayout
 *
 * @param ele
 */
function eleCenter(ele) {
	var _ele;
	if (ele instanceof jQuery) {
		_ele = ele;
	} else {
		_ele = $(ele);
	}
	var browserW = $(window).width(),
		browserH = $(window).height(),
		eleW = _ele
		.width(),
		eleH = _ele.height(),
		top = browserH > eleH ? (browserH - eleH) / 2 : 0,
		left = browserW > eleW ? (browserW - eleW) / 2 : 0;
	_ele.css({
		'top': top,
		'left': left
	});
}

function trim(obj) {
	return $.trim(obj);
}

function checkNum(e) {
	return e.keyCode == 8 || e.keyCode == 46 || (e.keyCode >= 48 && e.keyCode <= 57);
}

function replaceChar(obj, length) {
	if (length == undefined) {
		obj.val(obj.val().replace(/[^\d]+/, ''));
	} else {
		obj.val(obj.val().replace(/[^\d]+/, '').substring(0, length));
	}

}

function loadCard(_this, isShow) {
	_uid = _this.attr('uid');
	$.get(U('public/Index/card'), {
		uid: _uid
	}, function(data) {
		_card = $(data);
		if (isShow) {
			_this.parent().append(_card);
		}
	});

	_this.parent().find('.p-dialog').show();
}

/**
 * 分页控件
 * @param data
 * @param method 页数对应的js函数
 * @returns
 */
function createPage(data, method) {
	if (data.totalPages > 1) {
		var _page = $("<p class='paging'></p>");
		_page.append("<a class='prev' href='javascript:;' " + (data.nowPage > 1 ? "onclick='" + method.replace("_p", data.nowPage - 1) + "'" : "") + ">←</a>");
		var startIndex = 1;
		var endIndex = data.totalPages;
		if (data.nowPage > 4 && data.totalPages > 5) {
			startIndex = data.nowPage - 2;
			_page.append("<a href='javascript:;' onclick='" + method.replace("_p", 1) + "'>1</a>");
			_page.append("<a class='noborder' href='javascript:;'>...</a>");
		}
		if (data.nowPage < data.totalPages - 2) {
			if (data.nowPage < 5) {
				endIndex = data.totalPages <= 5 ? data.totalPages : 5;
			} else {
				endIndex = data.nowPage + 1;
			}
		}
		for (; startIndex <= endIndex; startIndex++) {
			_page.append("<a href='javascript:;' " + (data.nowPage == startIndex ? "class='active'" : "onclick='" + method.replace("_p", startIndex) + "'") + ">" + startIndex + "</a>");
		}
		if (endIndex < data.totalPages) {
			_page.append("<a class='noborder' href='javascript:;'>...</a>");
			_page.append("<a href='javascript:;' onclick='" + method.replace("_p", data.totalPages) + "'>" + data.totalPages + "</a>");
		}
		_page.append("<a class='prev' href='javascript:;' " + (data.nowPage < data.totalPages ? "onclick='" + method.replace("_p", data.nowPage + 1) + "'" : "") + ">→</a>");
		return _page;
	} else {
		return false;
	}
}