$(function(){
	$('.information .msg_tab').click(function(){
		$('#content').innerHeight($('#content').innerHeight());
		$('.information .msg_tab').removeClass('active');
		var _this=$(this);
		_this.addClass('active');
		$('.information .int-part').remove();
		if(_this.attr('id')=="comment"){
			_main=$("<div class='int-part part-sx'></div>");
			_main.append("<div class='tc p-loading' style='display:none;'><img src='"+THEME_URL+"/images/loading.gif' class='in-loading'></div>");
			_main.append("<ul class='int-part-ul'></ul>");
			$('.int-main').append(_main);
			updateComment();
		}else if(_this.attr("id")=='msg'){
			_main=$("<div class='int-part part-sx'></div>");
			_main.append("<div class='tc p-loading' style='display:none;'><img src='"+THEME_URL+"/images/loading.gif' class='in-loading'></div>");
			_main.append("<ul class='int-part-ul'></ul>");
			$('.int-main').append(_main);
			updateMsg();
		}else{
			_main=$("<div class='int-part part-tz'></div>");
			_main.append("<div class='tc p-loading' style='display:none;'><img src='"+THEME_URL+"/images/loading.gif' class='in-loading'></div>");
			_main.append("<ul class='int-part-ul'></ul>");
			$('.int-main').append(_main);
			updateNotify();
		}
	});
	$('.information .msg_tab.active').click();
})
function updateComment(p){
	var _ul=$('.information .int-part-ul');
	_ul.empty();
	$('.information .p-loading').show();
	$.get(U('public/Comment/commentList'),{p:(p?p:0)},function(res){
		resetMsg('unread_comment');
		$('.information .p-loading').hide();
		var list=res.data;
		for(var i in list){
			var first=list[i];
			var _li=$("<li></li>");
			var _part=$("<div class='int-part-con'></div>");
			var _avatar=$("<div class='int-part-avatar'></div>");
			_avatar.append("<a class='avatar tiny' href='"+U('public/Profile/index')+"&uid="+first.sender.uid+"'><img src='"+first.sender.avatar_url+"' alt='头像' title='"+first.sender.uname+"'></a>");
			_avatar.append("<p><a href='"+U('public/Profile/index')+"&uid="+first.sender.uid+"' class='cblue'>"+first.sender.uname+"</a></p>");				
			_part.append(_avatar);
			_part.append("<div class='int-part-txt'><p>"+(first.comment?first.comment:"")+"</p><h6><span class='time'>"+first.ctime+"</span></h6></div>");
			_li.append(_part);
			_li.append("<p class='tr'><a href='javascript:;' class='a-reply' onclick='toReplay(this)'>回复</a></p><p class='tr none p-reply'><input type='text' class='input-reply'><input type='button' class='btn btn-reply' onclick='replayComment(this,"+first.id+","+first.blog_id+",\""+first.sender.uname+"\")' value='回复'></p>");
			_ul.append(_li);
		}
		_ul.siblings('.paging').remove();
		var page=createPage(res,'updateComment(_p)');
		if(page){
			_ul.after(page);
		}
		//content height
		var minHeight = $(window).innerHeight() - $('#footer').innerHeight();
		$('#content').css("height","auto");
		$('#content').innerHeight() <= minHeight?$('#content').innerHeight(minHeight):$('#content').css("height","auto");
	},'json')
}
function updateMsg(p){
	var _ul=$('.information .int-part-ul');
	_ul.empty();
	$('.information .pl-nav a').removeClass('active');
	$('.information .p-loading').show();
	$.get(U('public/Message/indexList'),{p:(p?p:1)},function(res){
		resetMsg('unread_message');
		var list=res.data;
		for(var i in list){
			var one_chat=list[i];
			var _li=$("<li></li>");
			var _part=$("<div class='int-part-con'></div>");
			var _avatar=$("<div class='int-part-avatar'></div>");
			_avatar.append("<a class='avatar tiny' href='"+U('public/Profile/index')+"&uid="+one_chat.from_uid+"'><img src='"+one_chat.user_info.avatar_url+"' alt='头像' title='"+one_chat.user_info.uname+"'></a>");
			_avatar.append("<p><a href='"+U('public/Profile/index')+"&uid="+one_chat.from_uid+"' class='cblue'>"+one_chat.user_info.uname+"</a></p>");				
			_part.append(_avatar);
			_part.append("<div class='int-part-txt'><p>"+(one_chat.content?one_chat.content:"")+"</p><h6><span class='time'>"+one_chat.mtime+"</span></h6></div>");
			_li.append(_part);
			if(one_chat.from_uid!=MID){
				_li.append("<p class='tr'><a href='javascript:;' class='a-reply' onclick='toReplay(this)'>回复</a></p><p class='tr none p-reply'><input type='text' class='input-reply'><input type='button' class='btn btn-reply' onclick='replayMsg(this,"+one_chat.list_id+","+one_chat.from_uid+")' value='回复'></p>");
			}
			_ul.append(_li);
		}
		_ul.siblings('.paging').remove();
		var page=createPage(res,"updateMsg(_p)");
		if(page){
			_ul.after(page);
		}
		$('.information .p-loading').hide();
		//content height
		var minHeight = $(window).innerHeight() - $('#footer').innerHeight();
		$('#content').css("height","auto");
		$('#content').innerHeight() <= minHeight?$('#content').innerHeight(minHeight):$('#content').css("height","auto");
	},'json')
}
function updateNotify(p){
	var _ul=$('.information .int-part-ul');
	_ul.empty();
	$('.information .pl-nav a').removeClass('active');
	$('.information .p-loading').show();
	$.get(U('public/Message/notify'),{p:(p?p:1)},function(res){
		resetMsg('unread_notify');
		$('.information .p-loading').hide();
		var list=res.data;
		for(var i in list){
			var notify=list[i];
			var _li=$("<li></li>");
			var _part=$("<div class='int-part-con'></div>");
			var _text=$("<div class='int-part-txt'></div>");
			_text.append("<h6><span>"+notify.title+"</span><span class='time'> "+notify.ctime+"</span></h6>");
			_text.append("<p>"+notify.body+"</p>");
			
			
			_part.append(_text);
			_li.append(_part);
			_ul.append(_li);
		}
		_ul.siblings('.paging').remove();
		var page=createPage(res,"updateNotify(_p)");
		if(page){
			_ul.after(page);
		}
		//content height
		var minHeight = $(window).innerHeight() - $('#footer').innerHeight();
		$('#content').css("height","auto");
		$('#content').innerHeight() <= minHeight?$('#content').innerHeight(minHeight):$('#content').css("height","auto");
	},'json')
}
function toReplay(obj){
	obj=$(obj);
	var replay=obj.parent().next('p.p-reply');
	if(replay.is(':hidden')){
		replay.show();
	}else{
		replay.hide();
	}
}
function replayComment(obj,pre_id,blog_id,uname){
	var _this=$(obj);
	var url = "public/Profile/commentBlog";
	var comment=_this.siblings('input[type=text]').val().trim();
	if(comment.length<=0){
		dreambox.alert('请输入评论内容',null,3);
		return false;
	}
	_this.val('回复中...');
	comment="回复@"+uname+"："+comment;
	var data = {
		'ref_id': pre_id,
		'id': blog_id,
		'comment': comment
	};
	
	$.post(U(url), data, function(json){
		_this.parent().hide();
		$('#comment').click();
	},'json');
}
function replayMsg(obj,list_id,to_uid){
	var _this=$(obj);
	var url = "public/Profile/commentBlog";
	var reply_content=_this.siblings('input[type=text]').val().trim();
	if(reply_content.length<=0){
		dreambox.alert('不能发送空内容',null,3);
		return false;
	}
	_this.val('回复中...');
	var data = {
		'id': list_id,
		'reply_content': reply_content,
		'to': to_uid
	};
	$.post(U('public/Message/doReply'), data, function(res) {
		_this.parent().hide();
		if(res.status==1){
			$('#msg').click();			
		}else{
			dreambox.alert(res.data,null,3);
		}
	},'json');
}
function resetMsg(cl){
	$('.header-main .header-msg .'+cl).text('');
	var hasMsg=false;
	$('.header-main .header-msg .msg-num').each(function(){
		var txt=trim($(this).text());
		if(txt!=''){
			hasMsg=true;
		}
	});
	if(hasMsg){
		$("#msg_notify").removeClass('hasLetter letter').addClass("hasLetter");
	}else{
		$("#msg_notify").removeClass('hasLetter letter').addClass("letter");	
	}
}