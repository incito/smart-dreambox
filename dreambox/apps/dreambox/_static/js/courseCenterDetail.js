$(function() {
	$('.mxkc .mxkc-top p a.more').click(function(event) {
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

	$('.users')
			.click(
					function(event) {
						_this = $(event.target);
						// 取消关注
						if (_this.is('a .blue')) {
							if ($('#mid').val() == '0') {
								$('.toLogin').click();
								return;
							}
							_focus = _this.parents('p.focus');
							_fid = _focus.attr('fid');
							data = {
								'fid' : _fid
							}
							if (_this.text() == '取消') {
								var url = 'public/Follow/unFollow';
								$
										.post(
												U(url),
												data,
												function(json) {
													if (json.status) {
														var _sameFocus = '.focus[fid='+_fid+']';
														_focus.parents('.users').find(_sameFocus)
																.html('<a href="javascript:;"class="follow"><span class="yellow">+</span> <span class="blue">关注</span></a>');
													} else {
														alert("取消关注失败！");
													}
												}, 'json');
							} else {
								var url = 'public/Follow/doFollow';
								$
										.post(
												U(url),
												data,
												function(json) {
													if (json.status) {
														var _sameFocus = '.focus[fid='+_fid+']';
														_focus.parents('.users').find(_sameFocus)
																.html('<span>已关注</span><span class="mr5 ml5">|</span> <a href="javascript:;"class="follow"><span class="blue">取消</span></a>');
													} else {
														alert("关注失败！");
													}
												}, 'json');
							}
							return;
						}
					});

	window.Follow = {
		option : {// 配置参数
			curNum : 4,// 开始下标
			getNum : 4,// 请求个数
			cancelFollowStr : '<li><div class="top"><a class="avatar tiny" href="javascript:;"><img src="http://localhost/dreambox/addons/theme/stv1/_static/image/noavatar/small.jpg" alt="头像" title="XXX"></a><div class="top-info"><p><span class="name">hegy</span><span class="sex">男</span></p><p class="focus" fid="38"><span>已关注</span><span class="mr5 ml5"> | </span><a class="follow" href="javascript:;"><span class="blue">取消</span></a></p></div></div><p> 15小时前 选择了 全人教育Ⅱ </p></li>',
			followStr : '<li><div class="top"><a class="avatar tiny" href="javascript:;"><img src="http://localhost/dreambox/addons/theme/stv1/_static/image/noavatar/small.jpg" alt="头像" title="XXX"></a><div class="top-info"><p><span class="name">张老师</span><span class="sex">男</span></p><p class="focus" fid="36"><a class="follow" href="javascript:;"><span class="yellow"> + </span><span class="blue">关注</span></a></p></div></div><p> 14小时前 选择了 全人教育Ⅱ </p></li>'
		}
	}

	$('h6 a').click(function() {
		cid = $('#courseId').val();
		var option = Follow.option;
		var data = {
			'beginNum' : option.curNum,
			'getNum' : option.getNum,
			'course_id' : cid
		};
		var url = 'dreambox/CourseCenter/changeFollowUser';
		$.post(U(url), data, function(json) {
			if (json.status) {
				if (json.info == 0) {
					option.curNum = 0;
					return;
				}
				option.curNum += 4;
				var _users = $('.users');
				_users.empty();
				for ( var i in json.data) {
					var follow = json.data[i];
					var _div;
					if (follow.status == 1) {
						_div = $(option.cancelFollowStr);
					} else {
						_div = $(option.followStr);
					}
					var _href = U('public/Profile/index') + '&uid=' + follow.uid;
					_div.find('.avatar').attr('href', _href);
					_div.find('.avatar img').attr('title', follow.uname);
					_div.find('.avatar img').attr('src', follow.user_small);
					_div.find('.top-info .name').text(follow.uname);
					_div.find('.top-info .sex').text(follow.sex);
					_div.find('.focus').attr('fid', follow.uid);
					_div.find('> p').text(follow.time + " " + follow.result);
					if (i != 0) {
						var _line = $('<li class="li-line"></li>');
						_users.append(_line);
					}
					_users.append(_div);

				}
			} else {
				alert("操作失败！");
			}
		}, 'json');
	});
	// 添加标签
	$('.xkcgx').click(function() {
		if ($('#mid').val() == '0') {
			$('.toLogin').click();
			return;
		}
		Blog.add(function(id) {
			window.location.href = window.location.href;
		});
	});
})