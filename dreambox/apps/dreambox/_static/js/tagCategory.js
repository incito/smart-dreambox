$(function() {
	$('.index-nav-hd a>span').on('click', function(event) {
		event.preventDefault();
		$('.index-nav-hd a>span.active').removeClass('active');
		$(this).addClass('active');
	});

	// 条件过滤
	$('.index-nav-md a').click(function(event) {
		_this = $(event.target);
		_p = _this.parent('p');
		_p.find('a.active').removeClass('active');
		_this.addClass('active');
		_p.find('input').val(_this.attr('typeValue'));
		
		// 加载 全部文章动态
		var option = Blog.option;
		option.curNum = 0,
		option.getNum = 5,
		option.timeType = $('#timeType').val();
		option.orderType = $('#orderType').val();
		//清除原来博文
		$('.article-con').empty();
		loadAllBlog(option);
	});
	// 文章动态
	window.Blog = {
		option : {// 配置参数
			pageSize : 5,// 请求最大个数
			wHeight : $(window).height(),// 窗口高度
			dHeight : $(document).height(),// 文档高度
			scrollH : 0,// 上次滚动条位置
			bot : 0.1,// 触发数据请求距离
			isAjax : true,// 是否正在ajax请求
			curNum : 0,// 开始下标
			getNum : 5,// 请求个数
			timeType : 1,// 时间过滤类型
			orderType : 1,// 排序类型
			blogPart : '<div class="article-part"><h3 class="cont"><span>个子高，有多重要？<i class="icons jing"></i></span><span class="time">2014-06-22 12:22</span><div class="name" href="javascript:;"><span class="href">XX</span></div></h3><div class="conttiny"></div><div class="contall"></div><div class="contfooter"><p><span class="tags"><i class="i-icons tag"></i><a href="javascript:;">标签</a><a href="javascript:;">标签</a><a href="javascript:;">标签</a></span><a href="javascript:;" class="like fr"> <i class="p-icons like"></i>喜欢 (452) </a><a href="javascript:;" class="talk fr"> <i class="p-icons talk"></i>评论 (2) </a><a href="javascript:;" class="share fr"> <i class="p-icons share"></i>转载 (2) </a></p></div><div class="isaytalk" style="display: none"><i class="i-topdirection"></i><div class="isayinput"><input type="text"> <input type="button" value="发布"href="javascript:;"></div><div class="isaycon"><ul></ul></div><div class="isayusers-list"><ul></ul></div><p class="isaymore"><a href="javascript:;">收起</a></p></div></div>',
		}
	}
	function loadAllBlog(option) {
		var data = {
			'tag_id' : $('#tag_id').val(),
			'beginNum' : option.curNum,
			'getNum' : option.getNum,
			'time_type' : option.timeType,
			'order_type' : option.orderType
		};
		$.post(U('dreambox/TagCategory/getIndexBlog'), data, function(json) {
			fillData(option, json);
			var minHeight = $(window).innerHeight() - $('#footer').innerHeight();
			$('#content').css("height", "auto");
			$('#content').innerHeight() <= minHeight ? $('#content').innerHeight(
					minHeight) : $('#content').css("height", "auto");
		}, 'json');
	}
	// 滚动加载
	$(window)
			.scroll(
					function(event) {
						var option = Blog.option;
						option.scrollTop = $(window).scrollTop();
						option.wHeight = $(window).height();
						option.dHeight = $(document).height();
						if (option.isAjax) {
							return;
						}
						if ((option.scrollTop + option.bot * option.dHeight) >= (option.dHeight - option.wHeight)
								&& option.scrollTop > option.scrollH) {
							option.isAjax = true;
							loadAllBlog(option);
						}
						option.scrollH = option.scrollTop;
					});
	// 加载 全部文章动态
	var option = Blog.option;
	loadAllBlog(option);
});
