$(function() {
	initCss();
	function initCss() {
		_trs = $('.cc-part-main tr');
		for (i = 0; i < _trs.length; i++) {
			_tds = _trs.eq(i).find('td');
			for (j = 0; j < _tds.length; j++) {
				_td = _tds.eq(j);
				if (j == 0) {
					_td.addClass('tc');
				} else if (j % 2 == 1) {
					_td.addClass('even');
				}
				if (j == (_tds.length - 1)) {
					_td.addClass('nbdr');
				}
			}
		}

		_hds = $('.kchd-box');
		for (i = 1; i <= _hds.length; i++) {
			_hd = _hds.eq(i - 1);
			if (i % 2 == 1) {
				_hd.addClass('mr30');
			}
		}
	}

	$('.kcjs .cc-part-main >p a.more').click(function(event) {
		text = $(this).parent().find('span:first').text();
		sign = $(this).find('span').text();
		if (sign == '﹀') {
			$(this).html('收起<span>︿</span>');
		} else {
			$(this).html('展开<span>﹀</span>');
		}
		$(this).parent().find('span:first').text($(this).attr('title'));
		$(this).attr('title', text);
	});

	window.Course = {
		option : {// 配置参数
			pageSize : 5,// 请求最大个数
			wHeight : $(window).height(),// 窗口高度
			dHeight : $(document).height(),// 文档高度
			scrollH : 0,// 上次滚动条位置
			bot : 0.1,// 触发数据请求距离
			isAjax : true,// 是否正在ajax请求
			curNum : 0,// 开始下标
			getNum : 5,// 请求个数
			blogPart : '<div class="article-part"><h3 class="cont"><span class="cont-txt">个子高，有多重要？<i class="icons jing"></i></span><span class="time">2014-06-22 12:22</span><div class="name" href="javascript:;"><span class="href">XX</span></div></h3><div class="conttiny"></div><div class="contall"></div><div class="contfooter"><p><span class="tags"><i class="i-icons tag"></i><a href="javascript:;">标签</a><a href="javascript:;">标签</a><a href="javascript:;">标签</a></span><a href="javascript:;" class="like fr"> <i class="p-icons like"></i>喜欢 (452) </a><a href="javascript:;" class="talk fr"> <i class="p-icons talk"></i>评论 (2) </a><a href="javascript:;" class="share fr"> <i class="p-icons share"></i>转载 (2) </a></p></div><div class="isaytalk" style="display: none"><i class="i-topdirection"></i><div class="isayinput"><input type="text"> <input type="button" value="发布"href="javascript:;"></div><div class="isaycon"><ul></ul></div><div class="isayusers-list"><ul></ul></div><p class="isaymore"><a href="javascript:;">收起</a></p></div></div>'
		}
	}
	$(window)
			.scroll(
					function(event) {
						var option = Course.option;
						option.scrollTop = $(window).scrollTop();
						option.wHeight = $(window).height();
						option.dHeight = $(document).height();
						if (option.isAjax) {
							return;
						}
						if ((option.scrollTop + option.bot * option.dHeight) >= (option.dHeight - option.wHeight)
								&& option.scrollTop > option.scrollH) {
							option.isAjax = true;
							getCourseBlog(option);
						}
						option.scrollH = option.scrollTop;
					});

	function getCourseBlog(option) {
		var data = {
			'tag_id' : $('#tagId').val(),
			'beginNum' : option.curNum,
			'getNum' : option.getNum
		};
		$.post(U('dreambox/CourseCenter/courseBlogList'), data, function(json) {
			fillData(option,json);
			$('.kcdt .article-part').css('width','980px');
		}, 'json');
	}
	;
	getCourseBlog(Course.option);
})