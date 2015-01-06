window.Person ={
	option:{//配置参数
		pageSize:5,//请求最大个数
		isAjax:false,//是否正在ajax请求
		isScroll:true,//是否监听滚动
		isStopScroll:false,//是否停止监听winScroll
		curNum:0,//开始下标
		getNum:5,//请求个数
		bot:0.1,//触发数据请求距离
		timelineTop:0,//timeline top值
		scrollH:0,//上次滚动条位置
		year:0,//当前年份
		allYear:new Array(),//所有年份
		uid:0,//被访问的用户id
		mid:0,//登录的用户ID
		isSchool:false,//是否是学校账号
		scrollTop:$(window).scrollTop(),//window top
		wHeight:$(window).height(),//窗口高度
		dHeight:$(document).height(),//文档高度
		_timeLineDiv:$('.timeline-nav'),
		//mlistStr:'<div class="m-mlist"><label class="m-time"></label><i class="p-icons circle"></i><div class="isay close"><div class="opt"><a class="modify" title="编辑" href="javascript:;"><i class="p-icons modify"></i></a><a class="del" title="删除" href="javascript:;"><i class="p-icons del"></i></a></div><div class="triangle"><em class="t-o"></em><em class="t-i"></em></div><h3 class="isayt"></h3><div class="isaytiny"></div><div class="isayall img_resize"></div><div class="isayfooter"><p><a class="more fl" href="javascript:;"><span class="zk">展开 ﹀</span><span class="sq">收起︿</span></a><a class="like fr" href="javascript:;"></a><a class="talk fr" href="javascript:;"></a><a class="share fr" href="javascript:;"></a></p></div></div></div>',
		mlistStr:'<div class="m-mlist"><label class="m-time"></label><i class="p-icons circle"></i><div class="article-part"><div class="opt"><a href="javascript:;" class="modify" title="编辑"><i class="p-icons modify"></i></a><a href="javascript:;" class="del" title="删除"><i class="p-icons del"></i></a></div><div class="triangle"><em class="t-o"></em><em class="t-i"></em></div>'
				+'<h3 class="cont"><span class="cont-txt"><label></label><i class="icons jing"></i></span><span class="time"></span><div class="name"><span></span></div></h3>'
				+'<div class="conttiny"></div>'
				+'<div class="contall img_resize"></div>'
				+'<div class="contfooter">'
				+'	<p>'
				+'		<span class="tags"></span>'
				+'		<a href="javascript:;" class="like fr"></a>'
				+'		<a href="javascript:;" class="talk fr"></a>'
				+'		<a href="javascript:;" class="share fr"></a>'
				+'	</p>'
				+'</div>'
				+'</div>'
				+'</div>',
		//talkStr:'<div class="isaytalk"><i class="i-topdirection"></i><div class="isayinput"><input type="text"><input type="button" href="javascript:;" value="发布"></div><div class="isaycon"><ul></ul></div><!--p class="isaymore"><a href="javascript:;">查看更多</a></p--!><p class="isaymore"><a class="collapse" href="javascript:;">收起</a></p></div>',
		talkStr:'<div class="isaytalk">'
				+'  <i class="i-topdirection"></i>'
				+'  <div class="isayinput">'
				+'		<input type="text">'
				+'		<input type="button" value="发布" href="javascript:;">'
				+'	</div>'
				+'	<div class="isaycon">'
				+'		<ul></ul>'
				+'	</div>'
				+'	<p class="isaymore"><a class="collapse" href="javascript:;">收起</a></p>'
				+'</div>',
		usersStr:'<div class="isayusers"><i class="i-topdirection"></i><div class="isayusers-list"><ul></ul></div><p class="isaymore"><a class="collapse" href="javascript:;">收起</a></p></div>'
	},
	winScroll:function(){//window滚动监听事件
		$(window).scroll(function(event) {
			if(Person.option._timeLineDiv.length == 0){
				return;
			}
			var option = Person.option,
				timeLineT = option._timeLineDiv.parent().offset().top,
				timeLineL = option._timeLineDiv.parent().offset().left;
			option.scrollTop = $(window).scrollTop();
			option.wHeight = $(window).height();
			option.dHeight = $(document).height();
			Person.scrollTime(option);
			option._timeLineDiv.css({'position':(option.scrollTop >= timeLineT)?'fixed':'static','left':timeLineL});
			if(option.isAjax || !option.isScroll || option.isStopScroll) return;
			if((option.scrollTop+option.bot*option.dHeight) >= (option.dHeight - option.wHeight) && option.scrollTop > option.scrollH){
				option.isAjax = true;
				Person.getContent(option);
			}
			option.scrollH = option.scrollTop;
		});
	},
	scrollTime:function(option){//TimeLine 状态监听
		var dist = new Array(),min,j = 0,distnum;
		for(var i in option.allYear){
			var _div = $('#year-'+option.allYear[i]),
				minbot = _div.offset().top,
				maxbot = _div.offset().top + _div.height(),
				arr = new Array();
				arr.push(minbot);
				arr.push(maxbot);
				dist.push(arr);
		}
		distnum = Math.abs(option.scrollTop + (option.wHeight/2));
		for(;j < dist.length; j++){
			if(distnum > dist[j][0] && distnum <= dist[j][1]) break;
		}
		min = j === dist.length?0:j;
		option._timeLineDiv.find('li.active').removeClass('active');
		option._timeLineDiv.find('li[data-time="'+option.allYear[min]+'"]').addClass('active');
		option.year = option.allYear[min];
	},
	getContent:function(option){//获取文章数据
		var year = option.year;
		Person.option.isAjax = true;
		if(parseInt($('#year-'+option.year).attr('data-over')) === 1) {
			if(option.year == $('.timeline-nav .birthday').prev().attr('data-time').split('-')[0] && $('.m-mlist.last').size() === 0){
				Person.addEnd();
				option.isAjax = false;
				return;
			}else{
				option.year -=1;
				option.curNum = 0;
				Person.getContent(option);
				return;
			}
		}
		$('.person-box-r .loading').show(100);
		$.ajax({
            url: U('public/Profile/blogList'),
            data: {
           		'uid':option.uid,
           		'year': year,
				'beginNum': option.curNum,
				'pageSize': option.getNum,
				'isSchool':option.isSchool?1:0
			},
            type:"POST",
            dataType:'json',
            success: function(json){
            	var tList = json.data.list || [],
            		overCount = json.data.over_count,
            		_tList = new Array(),
					_pbr = $('.person-box-r ');
            		option.curNum = json.data.next_begin_number;
				if(overCount === 0){
					var cNum = option.getNum - tList.length;
					if(cNum > 0){
						 for(var i = 0;i < option.allYear.length;i++){
					 		if(parseInt(option.allYear[i]) === (year-1)){
					 			option.year = year - 1;
					 			option.getNum = cNum;
						 		option.curNum = 0;
					 			Person.getContent(option);
					 			break;
					 		}
						 }
						 if(i === option.allYear.length){
						 	if($('.m-mlist.last').size() === 0){
						 			Person.addEnd();
						 	}
						 	///return;
						 }
						 
					}else{
						option.getNum = option.pageSize;

					}
				}else if(option.getNum != option.pageSize){
					option.getNum = option.pageSize;
				}
				Person.fillBlog(option, tList, _tList);
            	_pbr.find('#year-'+year).attr('data-over',overCount === 0?1:0);//判断数据是否加载完：-1：未加载，0：加载一部分，1：:已加载完
            	_pbr.find('#year-'+year).append(_tList);
            	Person.option.isAjax = false;
            	$('.person-box-r .loading').hide(100);
            },
            error:function(){
            	Person.option.isAjax = false;
            	$('.person-box-r .loading').hide(100);
//            	alert('加载博文失败,请刷新页面重试!');
            },
            complete:function(){
            	Person.option.isAjax = false;
            	$('.person-box-r .loading').hide(100);
            }
        });
	},
	getMoreContent:function(option){
		var year = option.year;
		Person.option.isAjax = true;
		$('.person-box-r .loading').show(100);
		$.ajax({
            url: U('public/Profile/blogList'),
            data: {
            	'uid':option.uid,
            	'year': option.year,
				'beginNum': option.curNum,
				'pageSize': option.getNum,
				'isSchool':option.isSchool?1:0
			},
            type:"POST",
            dataType:'json',
            success: function(json){
            	var tList = json.data.list || [],
            		overCount = parseInt(json.data.over_count),
            		_tList = new Array(),
            		_pbr = $('.person-box-r');
        			Person.fillBlog(option, tList, _tList);
	            	_pbr.find('#year-'+year).attr('data-over',overCount === 0?1:0);//判断数据是否加载完：-1：未加载，0：加载一部分，1：:已加载完
	            	_pbr.find('#year-'+year).find('.m-mlist.needMore').before(_tList);
	            	if(overCount === 0){
	            		_pbr.find('#year-'+year).find('.m-mlist.needMore').remove();
	            	}
	            	Person.option.isAjax = false;
	            	$('.person-box-r .loading').hide(100);         		
            },
            complete:function(){
            	Person.option.isAjax = false;
            	$('.person-box-r .loading').hide(100);
            }
        });
	},
	fillBlog:function(option, tList, _tList){//填充博文的内容及操作
    	for(var index in tList){
			var _blog = tList[index],
				_div = $(option.mlistStr);
			_div.attr('id', _blog.id);
			_div.find('.jing').hide();//暂时直接隐藏加精的图标，后台功能还没实现
			//添加移动端发表的标识
			if(_blog.terminal==1){
				var mobileImg = '<img src="'+THEME_URL+'/images/wechat.png" height="35px" title="来自微信" />';
				_div.find('.cont .cont-txt').prepend(mobileImg);
			}
			var title = _blog.title;
			if(_blog.ref_id > 0){
				title = '【转】' + title;
			}
			_div.find('.cont label').html(title);
			_div.find('.m-time').text(_blog.cDate);
			_div.find('.time').text(_blog.cTime);
			_div.find('.name span').text(_blog.uname);
			_div.find('.name span').attr('href', U('public/Profile/index') + '&uid=' + _blog.uid);
			//绑定名片信息
			_div.find('.name span').attr('uid', _blog.uid);
			_div.find('.name span').toggleClass('href');
			
			var isaytiny = "";
			//博文中没有图片则不显示
			if(_blog.cover != null && _blog.cover != ""){
				isaytiny += '<img src="'+_blog.cover+'">';
			}
			isaytiny += _blog.content_short;
			var contall = '<span>'+_blog.content+'</span>';
			if(_blog.hasMore){
				isaytiny+='<a href="javascript:;" class="more">查看全文 <span>﹀</span>  </a>';
				contall+='<a href="javascript:;" class="open">收起 <span>︿</span>  </a>';
			}
			_div.find('.conttiny').html(isaytiny);
			_div.find('.contall').html(contall);
			//标签
			var _tags = "";
			for ( var j in _blog.tags) {
				var tag = _blog.tags[j];
				var _href = U('dreambox/TagCategory/index') + "&tag_id="
					+ tag.id
				_tags += '<a tag_id="'+tag.id+'" href="'+_href+'">'+tag.name+'</a>';
			}
			if(_tags != ""){
				_tags = '<i class="i-icons tag"></i>' + _tags;
			}
			_div.find('.tags').append(_tags);
			//处于登录状态才允许
			if(option.mid > 0){
				var _a = _div.find('.contfooter a.like');
				if(_blog.liked > 0){
					_a.html('<i class="p-icons like"></i>已喜欢('+_blog.likeCount+')');
					_a.removeClass('like');
					_a.toggleClass('liked');
				}else{
					_a.html('<i class="p-icons like"></i>喜欢('+_blog.likeCount+')');
				}
				
				_div.find('.contfooter a.talk').html('<i class="p-icons talk"></i>评论('+_blog.commentCount+')');
				
				_a = _div.find('.contfooter a.share');
				if(_blog.shared > 0){
					_a.html('<i class="p-icons share"></i>已转载('+_blog.republishCount+')');
					_a.removeClass('share');
					_a.toggleClass('shared');
				}else{
					_a.html('<i class="p-icons share"></i>转载('+_blog.republishCount+')');
				}
			}
			//控制编辑、删除权限
			if(option.mid != _blog.uid){
				_div.find(".opt").remove();
			}else if(_blog.ref_id > 0){//转载的文章不能编辑
				_div.find(".opt").find('.modify').remove();
			}
			
			_tList.push(_div);
    	}
	},
	addEnd:function(){
		var str = '<div class="m-mlist last"><label class="m-time">06-22</label><i class="p-icons circle"></i><div class="article-part"><h3 class="cont"><span>1992年4月12日生日</span> <a class="btnc xgsr" href="'+U("public/Account/index")+'">修改生日</a></h3><div class="isaytiny"><p></p></div></div></div>',
 			_div = $(str),
 			time = Person.option._timeLineDiv.find('li:last').attr('data-time').split('-'),
 			year = time[0],
 			month = time[1],
 			day = time[2];
 			_div.find('.cont span').text(year + '年' + month + '月' + day + '日生日 ');
 			_div.find('.m-time').text(month + '-' + day);
 			//访问他人主页不能修改生日
 			if(Person.option.uid != Person.option.mid){
 				_div.find('.xgsr').remove();
 			}
 			$('.person-box-r').append(_div);
 			Person.option.isStopScroll = true;
	},
	scrollTo:function(ele){//滚动动画
		var _body = (window.opera) ? (document.compatMode == "CSS1Compat" ? $('html') : $('body')) : $('html,body'),_ele;
		if (ele instanceof jQuery) {
			_ele = ele;
		} else {
			_ele = $(ele);
		}
		_body.animate({scrollTop: _ele.offset().top - (Person.option.wHeight/2)+10}, 1000,function(){
			Person.option.isScroll = true;
		});
	},
	addYearEvent:function(){
		Person.option._timeLineDiv.find('li').click(function(){
			var _this = $(this),
				year = _this.attr('data-time'),
		        _pres = _this.prevAll('li'),
		        years = new Array(),
		        curDiv = $('#year-'+year.substr(0,4)),
		        data,timer,
		        divStr = '<div class="m-mlist needMore"><p><a class="moreBlog" href="javascript:;">显示<span class="nm-time"></span>年的文章</a><a class="moreBlog" href="javascript:;"><i class="p-icons more"></i></a></p></div>';
		        Person.option.isScroll = false;
		        _pres.each(function(index, el) {
		        	years.push($(el).attr('data-time'));
		        });
		        for(var index in years){
		        	var _ele = $('#year-'+years[index]),
		        		isOver = parseInt(_ele.attr('data-over'));
		        	if((isOver === 0 || isOver === -1) && _ele.find('.needMore').size() === 0){
		        			var _div = $(divStr);
		        			_div.find('.nm-time').text(years[index]);
		        			_ele.append(_div);
		        	}
		        }
		        Person.option._timeLineDiv.find('li.active').removeClass('active');
		        $(this).addClass('active');
		        if(_this.hasClass('birthday')){
		        	if($('.m-mlist.last').size() === 0){
		        		Person.addEnd();
		        	}
		        	Person.scrollTo($('.m-mlist.last'));
		        	return;
		        }
		        if(curDiv.attr('data-over') == -1 && curDiv.find('.needMore').size() === 0){
		        	data = $.extend({},Person.option,{year:year,curNum:0}); 
		        	Person.getContent(data);
		        	timer = setInterval(!Person.option.isAjax?function(){} :function(){
		        		Person.scrollTo(curDiv);
		        		clearInterval(timer);
		        	},1000);
		        }else{
		        	Person.scrollTo(curDiv);
		        }
	        	
	        	
		})
	},
	addStartPoint:function(){
		var years = new Array(),str = '';
		Person.option._timeLineDiv.find('li:not(.birthday)').each(function(index, el) {
			var val = $(el).attr('data-time');
			if(val != null && val != ''){
				years.push(val);
			}
		});
		$.extend(Person.option,{allYear:years});
		for(var index  in years){
			str += '<div id="year-'+years[index]+'" class="year-point" data-over="-1"></div>';
		}
		$('.person-box-r').append(str);
	},
	prClick:function(){
		var personBox = $('.person-box-r');
		// 查看更多 优化
		personBox.on('click','.conttiny a.more',function(){
			var _this = $(this);
			_this.closest('.article-part').attr('scroll-to',$(document).scrollTop());
			_this.parents('.conttiny').hide();
			_this.parents('.conttiny').siblings('.contall').show();
		});

		// 点击图片展开 优化
		personBox.on('click','.conttiny img',function(){
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
		personBox.on('click','.contall a.open',function(){
			var _this = $(this);
			var part = _this.closest('.article-part');
			$(document).scrollTop(part.attr('scroll-to'));
			_this.parents('.contall').hide();
			_this.parents('.contall').siblings('.conttiny').show();
			part.removeAttr('scroll-to');
		});

		personBox.click(function(event){
			var option = Person.option;
			var _this = $(event.target);
			var mlist = _this.parents('.m-mlist');

			var data = {
				'id': mlist.attr('id')
			};
			if(_this.is('a.moreBlog') || _this.is('span.nm-time') || _this.is('i.p-icons.more')){//获取更多文章
				Person.showMore(_this);
				return;
			}
			//评论列表
			if(_this.is("a.talk")){
				Person.commentList(option, mlist, data);
				return;
			}
			//评论
			if(_this.is(":button")){
				var _comment = _this.parent().find(":text");
				if($.trim(_comment.val()) == ""){
					alert('评论内容不能为空！');
					return;
				}
				var url = "public/Profile/commentBlog";
				var data = {
					'ref_id': _comment.attr('ref_id'),
					'id': mlist.attr('id'),
					'uid': option.uid,
					'mid': option.mid,
					'comment': $.trim(_comment.val())
				};
				
				$.post(U(url), data, function(json){
					 _comment.val("");
					 var _str= mlist.find('a.talk').html();
					 var _start = _str.indexOf('(') + 1;
					 var _end = _str.indexOf(')');
					 var _num = parseInt(_str.substring(_start,_end)) + 1;
					 mlist.find('a.talk').trigger('click');
					 mlist.find('a.talk').html('<i class="p-icons talk"></i>评论('+_num+')');
				},'json');
				return;
			}
			//回复评论
			if(_this.is(".a-reply")){
				var id = _this.parent().attr('id'),
					user = _this.parent().find("img").attr('title');
				var text = _this.parents(".isaytalk").find(":text");
				text.attr('ref_id', id);
				text.val('回复@'+user+' ：');
				return;
			}
			//收起
			if(_this.is(".collapse")){
				var _div = _this.parents(".isaytalk");
				if(_div.is("div")){
					_div.remove();
					return;
				}
				_div = _this.parents(".isayusers");
				if(_div.is("div")){
					_div.remove();
				}
				return;
			}
			//
			var url = null;
			//本人查看
			var blog_uid = mlist.find('.name span').attr('uid');
			if(option.mid == blog_uid){
				//删除
				if(_this.is("a.del") || _this.parent().is("a.del")){
					dreambox.confirm("","您确定要删除这篇博文吗?","确认删除",function(){
						url = 'blog/Index/doDeleteBlog';
						$.post(U(url), data, function(json){
							mlist.remove();
						});
					});
					return;
				}
				//编辑
				if(_this.is("a.modify") || _this.parent().is("a.modify")){
					var _id = mlist.attr('id'),
					    _title = mlist.find('.cont label').html(),
					    _content = mlist.find('.contall > span').html();
					//填充内容
					Blog.edit(_id, _title, _content, function(){
						var url = "public/Profile/getBlog";
						$.post(U(url), data, function(json){
							var obj = json.data;
							mlist.find('.cont label').text(obj.title); 
							var isaytiny = "";
							//博文中没有图片则不显示
							if(obj.cover != null && obj.cover != ""){
								isaytiny += '<img src="'+obj.cover+'">';
							}
							isaytiny += obj.content_short;
							var contall = '<span>'+obj.content+'</span>';
							if(obj.hasMore){
								isaytiny+='<a href="javascript:;" class="more">查看全文 <span>﹀</span>  </a>';
								contall+='<a href="javascript:;" class="open">收起 <span>︿</span>  </a>';
							}else if(mlist.find('.contall').is(":visible")){
								contall+='<a href="javascript:;" class="open">收起 <span>︿</span>  </a>';
							}
							
							mlist.find('.conttiny').html(isaytiny);
							mlist.find('.contall').html(contall);
							// 处理标签
							mlist.find('.tags a').remove();
							for ( var j in obj.tags) {
								var tag = obj.tags[j];
								var _a = $('<a href="javascript:;">标签</a>');
								_a.attr('href', U('dreambox/TagCategory/index') + "&tag_id="
										+ tag.id);
								_a.html(tag.name);
								mlist.find('.tags').append(_a);
							}
							if (mlist.find('.tags a').length == 0) {
								mlist.find('.tags').remove();
							}
						},'json');
					});
					return;
				}
				//转载、喜欢
				if(_this.is("a.share")){
					url = "public/Profile/shareList";
				}else if(_this.is("a.like")){
					url = "public/Profile/likeList";
				} 
				//
				if(url == null){
					return;
				}
				
				//请求数据
				$.post(U(url), data, function(json){
					var content = "";
					var list = json.data;
					for(var i in list){
						var src = list[i].avatar_small,
							uname = list[i].uname,
							uid = list[i].uid;
						var href = U('public/Profile/index')+'&uid='+uid;
						content += '<li><a class="avatar tiny" href="'+href+'"><img title="'+uname+'" alt="头像" src="'+src+'"></a><p><a href="'+href+'">'+uname+'</a></p></li>';
					}
					var isayuser = $(option.usersStr);
					isayuser.find('ul').html(content);
					if(_this.is("a.like")){
						isayuser.find('.i-topdirection').toggleClass('like');
					}
					var _divUesrs = mlist.find('.isayusers'),
						_divTalk = mlist.find('.isaytalk');
					if(_divUesrs != ""){
						_divUesrs.remove();
					}
					if(_divTalk != ""){
						_divTalk.remove();
					}
					mlist.find('.article-part').append(isayuser);
				},'json');
			}else{
				//转载、喜欢
				var text = "";
				if(_this.is("a.share")){
					text = "转载";
					url = "public/Profile/shareBlog";
				}else if(_this.is("a.like")){
					text = "喜欢";
					url = "public/Profile/likeBlog";
				}
				//
				if(url == null){
					return;
				}
				
				data = {
					'id': mlist.attr('id'),
					'uid':option.uid,
					'mid':option.mid
				}
				$.post(U(url), data, function(json){
					if(json.data == -1){
						alert('"'+ text + "\"失败！");
						return;
					}
					if(text == "转载"){
						 var _str= mlist.find('a.share').html();
						 var _start = _str.indexOf('(') + 1;
						 var _end = _str.indexOf(')');
						 var _num = parseInt(_str.substring(_start,_end)) + 1;
						_this.html('<i class="p-icons share"></i>已成功' + text+'('+_num+')');
						_this.removeClass('share');
						_this.toggleClass('shared');
					}else if(text="喜欢"){
						 var _str= mlist.find('a.like').html();
						 var _start = _str.indexOf('(') + 1;
						 var _end = _str.indexOf(')');
						 var _num = parseInt(_str.substring(_start,_end)) + 1;
						_this.html('<i class="p-icons like"></i>已' + text+'('+_num+')');
						_this.removeClass('like');
						_this.toggleClass('liked');
					}
					
				},'json');
			}
		})
	},
	commentList:function(option, mlist, data){
		var url = "public/Profile/commentList";
		$.post(U(url), data, function(json){
			var content = "";
			var list = json.data;
			for(var i in list){
				var id = list[i].id,
					src = list[i].avatar_small,
					uname = list[i].uname,
					comment = list[i].comment,
					ctime = list[i].ctime,
					uid = list[i].uid;
				var href = U('public/Profile/index')+'&uid='+uid;
				content += '<li id="'+id+'"><a class="avatar tiny" href="'+href+'"><img title="'+uname+'" alt="头像" src="'+src+'"></a><div class="maincon"><span class="hfxm"><a href="javascript:;">'+uname+'</a></span><span class="hfnr">'+comment+'</span></div><label class="isaycon-time">'+ctime+'</label><a class="a-reply" href="javascript:;">回复</a></li>';
			}
			var isayuser = $(option.talkStr);
			isayuser.find('ul').html(content);
			var _divUesrs = mlist.find('.isayusers'),
				_divTalk = mlist.find('.isaytalk');
			if(_divUesrs != ""){
				_divUesrs.remove();
			}
			if(_divTalk != ""){
				_divTalk.remove();
			}
			mlist.find('.article-part').append(isayuser);
		},'json');
	},
	showMore:function(_this){
		var _parent = _this.closest('.year-point'),option;
		option = {
			'uid':Person.option.uid,
			'year':_parent.attr('id').substr(5,4),
			'curNum':_parent.find('.m-mlist').size()-1,
			'getNum':Person.option.pageSize
		}
		if(Person.option.isAjax){
			return;
		}else{
			Person.option.isAjax = true;
			option = $.extend({},Person.option,option); 
			Person.getMoreContent(option);
		}
	},
	init:function(uid, mid,isSchool){//初始化
		Person.option.uid = uid;
		Person.option.mid = mid;
		if(arguments.length>2){
			Person.option.isSchool = isSchool;
		}
		Person.option.scrollTop = $(window).scrollTop();
		Person.option.wHeight = $(window).height();
		Person.option.dHeight = $(document).height();
		Person.option.scrollH = Person.option.scrollTop;
		Person.option.year = Person.option._timeLineDiv.find('li.active').attr('data-time');
		Person.addStartPoint();
		Person.addYearEvent();
		Person.prClick();
		Person.scrollTime(Person.option);
		Person.winScroll();

		Person.getContent(Person.option);
	}
};

