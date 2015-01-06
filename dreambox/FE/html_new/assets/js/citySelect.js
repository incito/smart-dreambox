/*
Ajax 三级省市联动

settings 参数说明
-----
prov:省份
city:城市
dist:地区（县）
school:学校
provUrl:省份获取数据地址  
cityUrl:城市获取数据地址
distUrl:地区（县）获取数据地址
schoolUrl:学校获取数据地址
------------------------------ */   
(function($){
	$.fn.citySelect=function(settings){
		if(this.length<1){return;};

		// 默认值
		settings=$.extend({
			provUrl:"assets/js/citys.json",
			cityUrl:"assets/js/citys.json",
			distUrl:"assets/js/citys.json",
			schoolUrl:"assets/js/citys.json",
			prov:".prov",
			city:".city",
			dist:".dist",
			school:".school"
		},settings);

		var box_obj=this,
			prov_obj=box_obj.find(settings.prov),
			city_obj=box_obj.find(settings.city),
			dist_obj=box_obj.find(settings.dist),
			school_obj=box_obj.find(settings.school),
			city_json,
			select_prehtml='<div class="select-city-wrap"><a class="close" href="javascript:;"><i class="icons close"></i></a><div class="title"><a class="sc-part fta active" href="javascript:;">ABCDEF</a><a class="sc-part" href="javascript:;">GHIJ</a><a class="sc-part" href="javascript:;">KLMN</a><a class="sc-part" href="javascript:;">PRRSTUVW</a><a class="sc-part" href="javascript:;">XYZ</a></div><div class="sc-main"><div class="loading"><img src="images/loading.gif" alt="loading"></div></div></div>';
		function cityStart(inputType,city){
			if($('.select-city-wrap:visible').size() > 0){
				$('.select-city-wrap:visible').remove();
			}
			if(typeof(city)=="string"){
				$.getJSON(city,function(json){
					var str='',_sp,_obj;
					switch(inputType)
					{
						case 'prov':
							_obj = 	prov_obj;		  
						    break;
						case 'city':
							_obj = 	city_obj;
							break;
						case 'dist':
							_obj = 	dist_obj;
							break;
					    case 'school':
							_obj = 	school_obj;
							break;
						default:break;
					}
					_obj.parent().find('.select-city-wrap').remove();
					_obj.parent().append(select_prehtml);
					//遍历数据
					$.each(json, function(index, val) {
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
					_sp = _obj.parent().find('.select-city-wrap');
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

				});


			}
		}

		var init=function(){
			// 选择省份时发生事件
			prov_obj.bind("click",function(){
				cityStart('prov',settings.provUrl);
			});
			// 选择市时发生事件
			city_obj.bind("click",function(){
				cityStart('city',settings.cityUrl);
			});
			// 选择县时发生事件
			dist_obj.bind("click",function(){
				cityStart('dist',settings.distUrl);
			});
			// 选择学校时发生事件
			school_obj.bind("click",function(){
				cityStart('school',settings.schoolUrl);
			});
		};
		init();
		
	};
})(jQuery);