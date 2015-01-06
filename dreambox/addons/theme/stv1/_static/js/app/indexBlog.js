$(function() {

	// 查看更多 优化
	$('.article-con').on('click','.conttiny a.more',function(){
		var _this = $(this);
		_this.closest('.article-part').attr('scroll-to',$(document).scrollTop());
		_this.parents('.conttiny').hide();
		_this.parents('.conttiny').siblings('.contall').show();
	});

	// 点击图片展开 优化
	$('.article-con').on('click','.conttiny img',function(){
		var _this = $(this);
		_this.closest('.article-part').attr('scroll-to',$(document).scrollTop());
		_this.parents('.conttiny').hide();
		var contall = _this.parents('.conttiny').siblings('.contall');
		if(contall.find('a.open').length==0){
			contall.append($('<a href="javascript:;" class="open">收起 <span>︿</span>  </a>'));
		}
		contall.show();
	});

	// 收起 优化
	$('.article-con').on('click','.contall a.open',function(){
		var _this = $(this);
		var part = _this.closest('.article-part');
		$(document).scrollTop(part.attr('scroll-to'));
		_this.parents('.contall').hide();
		_this.parents('.contall').siblings('.conttiny').show();
		part.removeAttr('scroll-to');
	});


	$('.article-con')
			.click(
					function(event) {
						_this = $(event.target);
						
						if (_this.is('.contfooter a.like')) {
							if (_this.html().indexOf('已') != -1) {
								return;
							}
							blog = _this.parents('.article-part');
							var uid = blog.attr('uid');
							var blog_id = blog.attr('blog_id');
							var data = {
								'id' : blog_id,
								'uid' : uid
							}
							if (uid == $('#mid').val()) {
								$
										.post(
												U('public/Profile/likeList'),
												data,
												function(json) {
													var content = "";
													var list = json.data;
													for ( var i in list) {
														var src = list[i].avatar_small, uname = list[i].uname, uid = list[i].uid;
														var href = U('public/Profile/index')
																+ '&uid=' + uid;
														content += '<li><a class="avatar tiny" href="'
																+ href
																+ '"><img title="'
																+ uname
																+ '" alt="头像" src="'
																+ src
																+ '"></a><p><a href="'
																+ href
																+ '">'
																+ uname
																+ '</a></p></li>';
													}
													content = $(content);
													blog
															.find(
																	'.isaytalk .isayinput')
															.hide();
													blog
															.find(
																	'.isaytalk .isaycon')
															.hide();
													blog
															.find(
																	'.isaytalk .isayusers-list')
															.show();
													blog
															.find(
																	'.isaytalk .i-topdirection')
															.css('right',
																	'50px');
													ul = blog
															.find('.isaytalk .isayusers-list ul');
													ul.empty().append(content);
													blog.find('.isaytalk')
															.show();
												}, 'json');
								return;
							}
							return;
						}

						if (_this.is('.contfooter a.talk')) {
							talk = _this.parents('.article-part').find(
									'.isaytalk');
							var url = "public/Profile/commentList";
							blog = _this.parents('.article-part');
							var blog_id = blog.attr('blog_id');
							data = {
								'id' : blog_id
							}
							$
									.post(
											U(url),
											data,
											function(json) {
												var content = "";
												var list = json.data;
												for ( var i in list) {
													var src = list[i].avatar_small, uname = list[i].uname, comment = list[i].comment, ctime = list[i].ctime, uid = list[i].uid;
													var href = U('public/Profile/index')
															+ '&uid=' + uid;
													content += '<li id="'+list[i].id+'"><a class="avatar tiny" href="'
															+ href
															+ '"><img title="'
															+ uname
															+ '" alt="头像" src="'
															+ src
															+ '"></a><div class="maincon"><span class="hfxm"><a href='
															+ href
															+ '>'
															+ uname
															+ '</a></span><span class="hfnr">'
															+ comment
															+ '</span></div><label class="isaycon-time">'
															+ ctime
															+ '</label><a class="a-reply" href="javascript:;">回复</a></li>';
												}
												content = $(content);
												blog.find(
														'.isaytalk .isayinput')
														.show();
												blog.find('.isaytalk .isaycon')
														.show();
												blog
														.find(
																'.isaytalk .i-topdirection')
														.css('right', '130px');
												blog
														.find(
																'.isaytalk .isayusers-list')
														.hide();
												ul = blog
														.find('.isaytalk .isaycon ul');
												talk.show();
												//刷新评论数量
												_this.html('<i class="p-icons talk"></i>评论 ('+list.length+')');
												ul.empty().append(content);
											}, 'json');
							return;
						}
						if (_this.is('.contfooter a.share')) {
							if (_this.html().indexOf('已') != -1) {
								return;
							}
							blog = _this.parents('.article-part');
							var uid = blog.attr('uid');
							var blog_id = blog.attr('blog_id');
							data = {
								'id' : blog_id,
								'uid' : uid
							}
							if (uid == $('#mid').val()) {
								$
										.post(
												U('public/Profile/shareList'),
												data,
												function(json) {
													var content = "";
													var list = json.data;
													for ( var i in list) {
														var src = list[i].avatar_small, uname = list[i].uname, uid = list[i].uid;
														var href = U('public/Profile/index')
																+ '&uid=' + uid;
														content += '<li><a class="avatar tiny" href="'
																+ href
																+ '"><img title="'
																+ uname
																+ '" alt="头像" src="'
																+ src
																+ '"></a><p><a href="'
																+ href
																+ '">'
																+ uname
																+ '</a></p></li>';
													}
													content = $(content);
													blog
															.find(
																	'.isaytalk .isayinput')
															.hide();
													blog
															.find(
																	'.isaytalk .isaycon')
															.hide();
													blog
															.find(
																	'.isaytalk .isayusers-list')
															.show();
													blog
															.find(
																	'.isaytalk .i-topdirection')
															.css('right',
																	'180px');
													ul = blog
															.find('.isaytalk .isayusers-list ul');
													ul.empty().append(content);
													blog.find('.isaytalk')
															.show();
												}, 'json');
								return;
							}

							return;
						}
						// 发布评论
						if (_this.is('.isaytalk input[type=button]')) {
							var _comment = _this.parent().find(":text");
							if ($.trim(_comment.val()) == '') {
								alert('评论内容不能为空！');
								return;
							}
							var url = "public/Profile/commentBlog";
							var blog = _this.parents('.article-part');
							var uid = blog.attr('uid');
							var blog_id = blog.attr('blog_id');
							var data = {
								ref_id: _comment.attr('ref_id'),
								id : blog_id,
								uid: uid,
								mid: $('#mid').val(),
								comment: $.trim(_comment.val())
							};
							$.post(U(url), data, function(json) {
								_this.parent().find(":text").val("");
//								blog.find('.contfooter a.talk').html(
//										'<i class="p-icons talk"></i>评论 ('
//												+ json.data + ')');
								blog.find('.contfooter a.talk')
										.trigger('click');
							}, 'json');
							return;
						}
						if (_this.is('.isaytalk .isaymore a')) {
							_this.parents('.isaytalk').hide();
							return;
						}
						if (_this.is('.isaytalk .isaycon a.a-reply')) {
							var id = _this.parent().attr('id');
							var user = _this.parent().find("img").attr('title');
							var text = _this.parents(".isaytalk").find(":text");
							text.attr('ref_id', id);
							text.val('回复@'+user+' ：');
							return;
						}
					});

});
function fillData(option, json) {
	if (json.status == 1) {
		option.curNum += option.getNum;
		article = $('.article-con');
		for ( var i in json.data) {
			var blog = json.data[i];
			var _part = '.article-part[blog_id='+blog.id+']';
			if($(_part).length>0){
				continue;
			}
			var _div = $(option.blogPart);
			_div.attr('blog_id', blog.id);
			_div.attr('uid', blog.uid);
			if(blog.terminal==1){
				var _img = '<img src="'+THEME_URL+'/images/wechat.png" height="35px" title="来自微信" />';
				_div.find('.cont span:first').html(_img+blog.title);
			}else{
				_div.find('.cont span:first').text(blog.title);
			}
			_div.find('.cont .time').text(blog.cTime);
			_div.find('.href').text(blog.uname);
			_href = U('public/Profile/index') + '&uid=' + blog.uid;
			_div.find('.cont .name span').attr('href', _href);
			_div.find('.cont .name span').attr('uid', blog.uid);
			var continy = "";
			// 博文中没有图片则不显示
			if (blog.cover != null && blog.cover != "") {
				continy += '<img src="' + blog.cover + '">';
			}
			continy += blog.content_short;
			var contall=blog.content;
			if(blog.hasMore){
				continy+='<a href="javascript:;" class="more">查看全文 <span>﹀</span>  </a>';
				contall+='<a href="javascript:;" class="open">收起 <span>︿</span>  </a>';
			}
			_div.find('.conttiny').html(continy);
			_div.find('.contall').html(contall);
			// 处理标签
			_div.find('.tags a').remove();
			for ( var j in blog.tags) {
				var tag = blog.tags[j];
				var _a = $('<a href="javascript:;">标签</a>');
				_a.attr('href', U('dreambox/TagCategory/index') + "&tag_id="
						+ tag.id);
				_a.html(tag.name);
				_div.find('.tags').append(_a);
			}
			if (_div.find('.tags a').length == 0) {
				_div.find('.tags').remove();
			}
			// 为0时,表示未登陆
			if (json.info == 0) {
				_div.find('.contfooter  a.like').empty();
				_div.find('.contfooter  a.talk').empty();
				_div.find('.contfooter  a.share').empty();
			} else {
				if (blog.liked > 0) {
					_div.find('.contfooter  a.like').html(
							'<i class="p-icons like"></i>已喜欢 ('
									+ blog.likeCount + ')');
				} else {
					var _like = _div.find('.contfooter  a.like');
					_like.html(
							'<i class="p-icons like"></i>喜欢 (' + blog.likeCount
									+ ')');
					_like.attr('blog_id',blog.id);
					_like.attr('uid',blog.uid);
					_like.one('click',function(){
						var _this = $(this);
							var data = {
								'id' : _this.attr('blog_id'),
								'uid' : _this.attr('uid')
							}
							if (_this.attr('uid') != $('#mid').val()) {
							$.post(
									U('public/Profile/likeBlog'),
									data,
									function(json) {
										var _text = _this.html();
										var _liked = _text.substring(_text.indexOf('(')+1,_text.indexOf(')'));
										var _count = parseInt(_liked)+1;
										_this
												.html('<i class="p-icons like"></i>已喜欢('
														+ _count
														+ ')');
									}, 'json');
						}
					});
				}
				
				_div.find('.contfooter  a.talk').html(
						'<i class="p-icons talk"></i>评论 (' + blog.commentCount
								+ ')');
				if (blog.shared > 0) {
					_div.find('.contfooter  a.share').html(
							'<i class="p-icons share"></i>已转载 ('
									+ blog.republishCount + ')');
				} else {
					var _share = _div.find('.contfooter  a.share');
					_share.html(
							'<i class="p-icons share"></i>转载 ('
									+ blog.republishCount + ')');
					_share.attr('blog_id',blog.id);
					_share.attr('uid',blog.uid);
					_share.one('click',function(){
						var _this = $(this);
						var data = {
							'id' : _this.attr('blog_id'),
							'uid' : _this.attr('uid')
						}
						if (_this.attr('uid') != $('#mid').val()) {
							$.post(U('public/Profile/shareBlog'), data,
									function(json) {
										var _text = _this.html();
										var _shared = _text.substring(_text.indexOf('(')+1,_text.indexOf(')'));
										var _count = parseInt(_shared)+1;
										_this.text('已转载(' + _count + ')');
									}, 'json');
						}
					});
				}
			}
			article.append(_div);
		}
	}
	option.isAjax = false;
};