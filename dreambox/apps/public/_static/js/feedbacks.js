$(function() {
	// 文章动态
	window.Blog = {
		option : {// 配置参数
			wHeight : $(window).height(),// 窗口高度
			dHeight : $(document).height(),// 文档高度
			scrollH : 0,// 上次滚动条位置
			bot : 0.1,// 触发数据请求距离
			isAjax : false,// 是否正在ajax请求
			curNum : 0,// 开始下标
			searchKey : '',// 搜索条件
		}
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
							loadData(option);
						}
						option.scrollH = option.scrollTop;
					});
	loadData(Blog.option);
	
	function loadData(option){
		option.isAjax=true;
		var _load=$(".person-box-r .loading ");
		_load.show();
		var data = {
				'p' : option.curNum+1
			};
		$.post(U('public/Profile/feedbacks'), data, function(json) {
			if(json.status==1){
				fillData(option, json.data);
			}
			option.isAjax=json.data.nowPage>=json.data.totalPages;
			_load.hide();
		}, 'json');
		
	}
	function fillData(option, json){
		option.curNum=json.nowPage;
		var data=json.data;
		var _ul=$(".detailed ul",".signinWith");
		if(data.length==0){
			_ul.append("<li>没有签到记录数据</li>");
			return false;
		}
		for(var i in data){
			var _status="";
			switch(parseInt(data[i].status)){
			case 0:
				_status="<div class='untreated'>未处理</div>";
				break;
			case 1:
				_status="<div class='pass'>通过</div>";
				break;
			case 2:
				_status="<div class='error'>未通过</div>";
				break;
			}
			_li="<li><div class='time'><i></i>"+data[i].lesson_time+" </div><div class='info clearfix'><div class='fl'>"+data[i].grade_name+data[i].class_name+"班"+data[i].course_name+"/"+data[i].hours_name+" "+data[i].section_num+"</div> <div class='fr'>"+_status+"</div></div></li>";
			_ul.append(_li);
		}
	}
})
