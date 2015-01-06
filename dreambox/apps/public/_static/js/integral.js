$(function(){
	//绑定日历控件
	$('.jfmx .begin').datepick({dateFormat: 'yy-mm-dd'});
	$('.jfmx .end').datepick({dateFormat: 'yy-mm-dd'});
	//积分明细、积分规则切换
	$('.userList-mid-box .title span').click(function(){
		var _this = $(this);
		$('.userList-mid-box .title span.active').removeClass('active');
		_this.addClass('active');
		$('.intergral-part').hide().eq(_this.index()).show();
	});
	
	//积分明细搜索
	$('.intergral-search .cx').click(function(event){
		displayDetail(1);
	});
	
	//积分明细分页
	$('.jfmx').find('.paging').click(function(event){
		var _this = $(event.target);
		if(_this.is('a')){
			var page = _this.attr('data');
			displayDetail(page);
		}
	});
	//默认加载第一页
	displayDetail(1);
	
});
	
function displayDetail(page){
	if(page > 0){
		var _div = $('.jfmx');
		//
		_div.find("tbody").html('');
		$('.p-loading').show();
		//请求数据
		var data = {
			'uid' : Person_head.option.uid,
			'p' : page,
			'begin' : _div.find('.begin').val(),
			'end' : _div.find('.end').val(),
			'type' : _div.find('.select').attr('date-value')
		}
		$.post(U('public/Profile/integralDetail'), data, function(json){
			var count = json.data.count,
				totalPages = json.data.totalPages,
				list = json.data.data || [],
				array = new Array();
			for(var i in list){
				var tr = $("<tr></tr>");
				tr.append('<td>'+list[i].type+'</td');
				tr.append('<td>'+list[i].increase_integral+'</td');
				tr.append('<td>'+list[i].comment+'</td');
				tr.append('<td>'+list[i].ctime+'</td');
				if(i % 2 == 1){
					tr.toggleClass('odd');
				}
				array.push(tr);
			}
			//
			$('.p-loading').hide();
			
			_div.find("tbody").html(array);
			_div.closest('#content').css('display','inline-block');//触发IE8重绘
			
//			if(totalPages > 1){
//				var array = new Array();
//				array.push('<a class="prev" href="javascript:;">←</a>');
//				for(var i = 1; i <= totalPages; i++){
//					var a = $('<a href="javascript:;" data=' + i + '>' + i + '</a>');
//					if(page == i){
//						a.toggleClass('active');
//					}
//					array.push(a);
//				}
//				array.push('<a class="next" href="javascript:;">→</a>');
//				_page.html(array);
//				_page.show();
//				
//				//
//				var pre = parseInt(page) - 1;
//				if(pre < 1){
//					pre = 1;
//				}
//				var next = parseInt(page) + 1;
//				if(next > totalPages){
//					next = totalPages;
//				}
//				_page.find('.prev').attr('data', pre);
//				_page.find('.next').attr('data', next);
//			}else{
//				_page.hide();
//			}
			if (count > 0){
				_div.find('.table span').hide();
			} else {
				_div.find('.table span').show();
			}
			
			var _ul = _div.find('.table');
			_ul.siblings('.paging').remove();
			var page=createPage(json.data,"displayDetail(_p)");
			if(page){
				_ul.after(page);
			}
			
		},'json');
	}
}