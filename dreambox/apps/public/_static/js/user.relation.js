$(function(){
	//切换关注、粉丝、访客选项卡
	$('.userList-mid-box .title span').click(function(){
		window.location.href = $(this).attr('href');
	});
	
	var _wgz = '<a href="javascript:;" class="focus gz">关注</a><span class="mr10 ml10 blue">|</span><a  href="javascript:;" href="" class="msg">私信</a>';
	var _ygz = '<span>已关注</span><span class="mr10 ml10">|</span><a class="focus qx" href="javascript:;">取消</a><span class="mr10 ml10 blue">|</span><a class="msg" href="javascript:;">私信</a>';
	
	$('.userList-part-ul').find('.fr.op').each(function(){
		var _this = $(this);
		if(_this.attr('status') == 0){
			_this.html(_wgz);
		}else{
			_this.html(_ygz);
		}
	});
	
	//关注、取消、私信
	$('.userList-part-ul').click(function(event){
		var _this = $(event.target),
			_div = _this.parents('.fr.op'),
		    _uid = _this.parents('li').attr('uid');
		if(_this.is('a.focus.gz')){
			var url = 'public/Follow/doFollow';
			data = {
				'fid' : _uid 	
			}
			$.post(U(url), data, function(json){
				if(json.status){
					_div.html(_ygz);
				}else{
					alert("关注失败！");
				}
			},'json');
		}else if(_this.is('a.focus.qx')){
			var url = 'public/Follow/unFollow';
			data = {
				'fid' : _uid
			}
			$.post(U(url), data, function(json){
				if(json.status){
					_div.html(_wgz);
				}else{
					alert("取消关注失败！");
				}
			},'json');
		}else if(_this.is('a.msg')){
			data = {
				'uid' : _uid
			}
			$.get(U('public/Message/send'), data, function(data){
				$('#inner_box').append(data);
			});
		}
		
	})
})