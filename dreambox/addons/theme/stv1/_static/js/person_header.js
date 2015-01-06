window.Person_head ={
	option:{//配置参数
		uid:0,//被访问的用户id
	},	
	following:function(){
		var _gz = $("p.line4").find('.gz'),
			_ygz = $("p.line4").find('.ygz'),
			_state = $("#follow_state").val();
		if(_state == 0){
			_gz.show();
		}else{
			_ygz.show();
		}
		_ygz.mouseover(function(event){
			var _this = $(event.target);
			if(_this.is('a')){
				_this.html('<span class="yellow">-</span>取消关注');
			}
		});
		_ygz.mouseout(function(event){
			var _this = $(event.target);
			if(_this.is('a')){
				_this.html('<span class="yellow">√</span>已关注');
			}
		})
		$("p.line4").click(function(event){
			var _this = $(event.target);
			if(_this.is('.gz')){
				var url = 'public/Follow/doFollow';
				data = {
					'fid' : Person_head.option.uid 	
				}
				$.post(U(url), data, function(json){
					if(json.status){
						_this.hide();
						_ygz.show();
					}else{
						alert("关注失败！");
					}
				},'json');
			}else if(_this.is('.ygz')){
				var url = 'public/Follow/unFollow';
				data = {
					'fid' : Person_head.option.uid 	
				}
				$.post(U(url), data, function(json){
					if(json.status){
						_this.hide();
						_gz.show();
					}else{
						alert("取消关注失败！");
					}
				},'json');
			}
		})
	},
	sendMessage:function(){
		$('.btnc.sx').click(function(){
			data = {
				'uid' : Person_head.option.uid
			}
			$.get(U('public/Message/send'), data, function(data){
				$('#inner_box').append(data);
				eleCenter($('.dialog-box').last());// 居中
			});
		})
	},
	init:function(uid){
		Person_head.option.uid = uid;
		
		Person_head.following();
		Person_head.sendMessage();
	}
}