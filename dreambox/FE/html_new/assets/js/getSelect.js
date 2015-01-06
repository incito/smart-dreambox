(function($){
	$.extend({
		getSelect:function(url){
			var url = url,
				str = '',
				_sp=$('<div class="select-city-wrap"><a class="close" href="javascript:;"><i class="icons close"></i></a><div class="title"><a class="sc-part fta active" href="javascript:;">ABCDEF</a><a class="sc-part" href="javascript:;">GHIJ</a><a class="sc-part" href="javascript:;">KLMN</a><a class="sc-part" href="javascript:;">PRRSTUVW</a><a class="sc-part" href="javascript:;">XYZ</a></div><div class="sc-main"><div class="loading"><img src="images/loading.gif" alt="loading"></div></div></div>');
				$.each(url, function(index, val) {
					if(index == 'A' || index == 'G' || index == 'K' || index == 'P' || index == 'X'){
						str += '<div class="sc-main-part">';
					}
					str = str + '<p><span>'+index+'</span>';
					for(i in val){
						str = str + '<a href="javascript:;">'+ (val[i].n == undefined?'未开放':val[i].n) + '</a>';
					}
					if(val == '' || val == null || val == undefined){
						str += '未开放';
					}
					str += '</p>';
					if(index == 'F' || index == 'J' || index == 'N' || index == 'W' || index == 'Z'){
						str += '</div>';
					}
				});
				_sp.find('.sc-main').append(str);
				_sp.find('.title .sc-part:eq(0)').addClass('active');
				_sp.find('.loading').hide();
				_sp.find('.sc-main-part:eq(0)').show();
				//头部hover状态
				_sp.find('.title .sc-part').hover(function() {
					var index = $(this).index();
					$(this).parents('.title').find('.sc-part.active').removeClass('active');
					$(this).addClass('active');
					_sp.find('.sc-main-part').hide();
					_sp.find('.sc-main-part').eq(index).show();
				}, function() {

				});
				//选择值
				_sp.find('.sc-main').click(function(event) {
					var _this = $(event.target);
					if(_this.is('a') && !_this.hasClass('.sc-part')){
						_obj.val(_this.text());
						_sp.hide();
					}
				});
				//关闭按钮
				_sp.find('a.close').click(function(event) {
					_sp.hide();
				});

		}
	});
})(jQuery);

