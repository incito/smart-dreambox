$(function() {
	var minHeight = $(window).innerHeight() - $('#footer').innerHeight();
	$('#content').css("height","auto");
	$('#content').innerHeight() <= minHeight?$('#content').innerHeight(minHeight):$('#content').css("height","auto");
	// 点击空白处
	$(document).click(
			function(event) {
				if($('.reply-form:hidden').length>0){
					return;
				}
				var _this = $(event.target);
				var _bool = _this.is('.saysay')
						|| _this.parents('.saysay').length > 0
						|| _this.is('a.tag-item')
						|| _this.is('a.tag-item span')
						|| _this.is('.tags input');
				var _isDialog = _this.parents('.infoBox').length > 0;
				if (!_bool && !_isDialog) {
					var _content = $("iframe.ke-edit-iframe").contents().find(
							"body");
					var title = trim($('#title').val());
					var content = trim(_content.html());
					if (title == "" && (content == "<br>" || content == "<p><br></p>" || content == "")) {
						// 收起
						$('.reply-form').slideUp();
					}

				}
			});
	if (MID != 0) {
		$
				.get(
						U('public/Index/isTeacherFirstLogin'),
						null,
						function(res) {
							if (res.status == 1) {
								var $div = $("<div class='dialog  integralDialog'> <div class='overlayer'></div> <div class='dialog-box' style='display: block;'> <h1 class='title'><a class='close fr' href='javascript:;'><i class='icons close'></i></a></h1> <div class='dialog-main'> <img src='"
										+ THEME_URL
										+ "/images/pic6.png' alt='恭喜' title='恭喜'> <h6>恭喜你！</h6> <p>成为梦想老师，请到主页给本学期排课！</p> </div> <div class='dialog-footer'> <p class='tc'><input class='btn footer-submit' type='submit' value='马上去排课'></p> </div> </div> </div> ");
								$div.find('.btn.footer-submit').click(
										function() {
											$div.remove();
											if ($('#selectCourse').length > 0) {
												$('#selectCourse').click();
											} else {
												$('a.wdkb-item').eq(3).click();
											}
										});
								$div.find('a.close').click(function() {
									$div.fadeOut(400, function() {
										$div.remove();
									})
								})
								$div.hide();
								$('body').append($div);
								$div.fadeIn(400);
								eleCenter($('.dialog-box').last());// 居中

							}
						}, 'json');
		$
				.get(
						U('public/Index/isNotifySchoolAdmin'),
						null,
						function(res) {
							if (res.status == 1) {
								var $div = $("<div class=\"overlayer\"></div><div class=\"dialog-box infoBox\" style=\"display: block;z-index: 999999;\"><h1 class=\"title\">欢迎！</h1><div class=\"dialog-main\"><p>当前学期需要确认！</p><br>"
										+ "</div><div class=\"dialog-footer\"><p><input class=\"btn footer-cancel\" type=\"button\" value=\"下次确认\" /><input class=\"btn footer-submit fr\" type=\"button\" value=\"立即确认\" /></p></div></div>");
								$('div.overlayer').css("z-index", '999999');
								$div
										.find('.btn.footer-cancel')
										.click(
												function() {
													$div.remove();
													$('div.overlayer').css(
															"z-index", '999');
													var url = U('public/Index/needNotifySchoolAdmin');
													$.get(url);
												})
								$div
										.find('.btn.footer-submit.fr')
										.click(
												function() {
													$div.remove();
													$('div.overlayer').css(
															"z-index", '999');
													var url = U('dreambox/Course/isShowTerm');
													$
															.post(
																	url,
																	null,
																	function(
																			data) {
																		if (data == 'showCourse') {
																			location.href = U('dreambox/Course/showCourse');
																		} else {
																			$(
																					'#inner_box')
																					.hide()
																					.html(
																							data);
																			eleCenter('#inner_box');
																			$(
																					'#inner_box')
																					.show();
																			// window.location
																			// =
																			// href1
																			// +
																			// "#termInfo";
																		}
																	});
												})
								$div.hide();
								$('body').append($div);
								eleCenter($('.dialog-box').last());// 居中
								$div.fadeIn(400);

							}
						}, 'json');

		// 签到
		$.get(U('dreambox/LessonFeedback/needFeedback'), null, function(data) {
			if(data.code==0){
				return false;
			}
			if (data.code == 1) {
				$.get(U('dreambox/LessonFeedback/show'), null, function(data) {
					var $div = $(data);
					$('body').append($div);
					eleCenter($('.dialog-box').last());// 居中
					$div.fadeIn(400);
				});
			}else if(data.code == 2){
				var $div = $(data.data);
				$('body').append($div);
				eleCenter($('.modal'));// 居中
				$div.fadeIn(400);
			}
		},'json');
		$("#show_confirm").click(function(){
			$.post(U("dreambox/LessonFeedback/confirm_list"),"view=1",function(data){
				var $div = $(data);
				$('body').append($div);
				eleCenter($('.modal'));// 居中
				$div.fadeIn(400);
			})
		})
	}
	var index = $('.article-hd-item').index($('.article-hd-item.active'));
	$('.i-icons.line').css('left', index * 120 + 40);
	$('.article-hd-item').click(function(event) {
		var _this = $(this);
//		if (_this.hasClass('active')) {
//			return;
//		}
		var option = Blog.option;
		if (option.isAjax) {
			alert('内容正在加载中,请稍后再试');
			return;
		}
		$('.i-icons.line').stop().animate({
			'left' : _this.index() * 120 + 40
		}, 200, function(event) {
			option.type = _this.attr('type');
			option.curNum = 0;
			$('.article-con').empty();
			option.isAjax = true;
			loadAllBlog(option);
		});
		$('.article-hd-item.active').removeClass('active');
		_this.addClass('active');
	});
	$('.article-hd-item').hover(function() {
		var _this = $(this);
		$('.i-icons.line').stop().animate({
			'left' : _this.index() * 120 + 40
		}, 200, function() {
		});
	}, function() {
		var _active = $('.article-hd-item.active');
		$('.i-icons.line').stop().animate({
			'left' : $('.article-hd-item').index(_active) * 120 + 40
		}, 200, function() {
		});
	});

	$('#login').click(function() {
		$('a.toLogin').trigger('click');
	});
	$('#reg').click(function() {
		$('a.toReg').trigger('click');
	});
	// 点击分享故事
	$('.saysay .shareStory').click(
			function() {
				_this = $(this);
				url = U('public/index/getTempBlog');
				$.post(url, null, function(json) {
					if (json.status > 0) {
						$('#title').val(json.data.title);
						$('#title').attr('status', json.data.status);
						$('#title').attr('blog_id', json.data.id);
						$("iframe.ke-edit-iframe").contents().find("body")
								.html(json.data.content);
					}
				}, 'json');
				$('.reply-form').slideDown(500);
			});
	// 发布文章
	$('.reply-form-row input.fb').click(function() {
		var _this = $(this);
		if (_this.val() == '发布中...') {
			return;
		}
		_this.val('发布中...');
		var url = U('blog/Index/doAddBlog');
		var id = $('#title').attr('blog_id');
		if (id != "") {
			url = U('blog/Index/doUpdate');
		}
		var _content = $("iframe.ke-edit-iframe").contents().find("body");
		var items = $('.tag-textarea .tag-item');
		var tags = "";
		for (i = 0; i < items.length; i++) {
			tags += items.eq(i).attr('id');
			tags += '-';
		}
		var data = {
			'id' : $('#title').attr('blog_id'),
			'uid' : $('#mid').val(),
			'title' : $('#title').val(),
			'content' : _content.html(),
			'cTime' : 1,
			'tags' : tags,
			'delTemp' : true //删除草稿

		}
		$.post(url, data, function(json) {
			if (json.status == 0) {
				dreambox.alert(json.info,function(){
					_this.val('发布');
				});
			} else if (json.status == 1) {
				$('.reply-form').slideUp();
				$('#title').val('标题');
				$('#title').attr('blog_id', '');
				_content.html('');
				dreambox.dialog('提示', json.info, function() {
					$('.tag-textarea .tag-item').remove();
					$('.tag-textarea .pla').show();
					$('.reply-info').parent().show();
					window.location.href = window.location.href;
				});
			}
		}, 'json');
	});
	// 保存为草稿
	$('.reply-form-row a#saveDraft').click(function() {
		var url = U('blog/Index/doAddBlog');
		var id = $('#title').attr('blog_id');
		if (id != "") {
			url = U('blog/Index/doUpdate');
		}
		var _content = $("iframe.ke-edit-iframe").contents().find("body");
		var items = $('.tag-textarea .tag-item');
		var tags = "";
		for (i = 0; i < items.length; i++) {
			tags += items.eq(i).attr('id');
			tags += '-';
		}
		var data = {
			'id' : id,
			'uid' : $('#mid').val(),
			'title' : $('#title').val(),
			'content' : _content.html(),
			'status' : 3,
			'tags' : tags
		}
		$.post(url, data, function(json) {
			if (json.status == 1) {
				$('#title').attr('blog_id', json.data.id);
				dreambox.dialog('提示', '保存成功');
			} else {
				dreambox.alert(json.info);
			}
		}, 'json');
	});

	// 选课
	$('#selectCourse').click(function() {
		var url = U('dreambox/Course/isShowTerm');
		$.post(url, null, function(data) {
			if (data == 'showCourse') {
				location.href = U('dreambox/Course/showCourse')+'&uid='+$('#uid').val();
			} else {
				$('#inner_box').hide().html(data);
				eleCenter('#inner_box');
				$('#inner_box').show();
				// window.location = href1 + "#termInfo";
			}
		});

	});
	$('#modifyTerm').click(function() {
		var url = U('dreambox/Term/showTerm');
		$.post(url, null, function(data) {
			$('#inner_box').hide().html(data);
			eleCenter('#inner_box');
			$('#inner_box').show();
			$('#inner_box').append($('<input type="hidden" id="term"/>'));
		});
	});
	// 管理课程
	$('a.wdkb-item').eq(3).click(function() {
		var url = U('dreambox/Course/isShowTerm');
		$.post(url, null, function(data) {
			if (data == 'showCourse') {
				location.href = U('dreambox/Course/showCourse')+'&uid='+$('#uid').val();
			} else {
				$('#inner_box').hide().html(data);
				eleCenter('#inner_box');
				$('#inner_box').show();
				//标识是否跳转，如果有该id则表示为不跳转
				$('#term').remove();
			}
		});
	});

	// 签到
	$('#qiandao').click(function() {
		$.get(U('dreambox/LessonFeedback/show'), null, function(data) {
			var $div = $(data);
			$('body').append($div);
			eleCenter($('.dialog-box').last());// 居中
			$div.fadeIn(400);
		});
	});
	// 搜索文章
	$('.aht-search a').click(function() {
		searchBlog();
	});

	$('.aht-search input').keydown(function(e) {
		if (e.keyCode == 13) {
			searchBlog();
		}
	});
	function searchBlog() {
		var option = Blog.option;
		option.curNum = 0;
		option.searchKey = trim($('.aht-search input').val());
		$('.article-con').empty();
		loadAllBlog(option);
	}
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
			type : 1,// 1为全部动态 2为热门推荐 3为我喜欢的
			searchKey : '',// 搜索条件
			blogPart : '<div class="article-part"><h3 class="cont"><span class="cont-txt">个子高，有多重要？<i class="icons jing"></i></span><span class="time">2014-06-22 12:22</span><div class="name" href="javascript:;"><span class="href">XX</span></div></h3><div class="conttiny"></div><div class="contall"></div><div class="contfooter"><p><span class="tags"><i class="i-icons tag"></i><a href="javascript:;">标签</a><a href="javascript:;">标签</a><a href="javascript:;">标签</a></span><a href="javascript:;" class="like fr"> <i class="p-icons like"></i>喜欢 (452) </a><a href="javascript:;" class="talk fr"> <i class="p-icons talk"></i>评论 (2) </a><a href="javascript:;" class="share fr"> <i class="p-icons share"></i>转载 (2) </a></p></div><div class="isaytalk" style="display: none"><i class="i-topdirection"></i><div class="isayinput"><input type="text"> <input type="button" value="发布"href="javascript:;"></div><div class="isaycon"><ul></ul></div><div class="isayusers-list"><ul></ul></div><p class="isaymore"><a href="javascript:;">收起</a></p></div></div>',
		}
	}
	function init() {
		// 加载 全部文章动态
		var option = Blog.option;
		option.searchKey = trim($('.aht-search input').val());
		loadAllBlog(option);
	}
	function loadAllBlog(option) {
		var data = {
			'type' : option.type,
			'beginNum' : option.curNum,
			'getNum' : option.getNum,
			'searchKey' : option.searchKey
		};
		$.post(U('public/Index/getIndexBlog'), data, function(json) {
			fillData(option, json);
			var minHeight = $(window).innerHeight() - $('#footer').innerHeight();
			$('#content').css("height","auto");
			$('#content').innerHeight() <= minHeight?$('#content').innerHeight(minHeight):$('#content').css("height","auto");
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
	init();
})