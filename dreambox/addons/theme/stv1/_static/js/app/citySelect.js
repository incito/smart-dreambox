var opt={
		areaUrl:U('public/Area/getAreaWithFL'),
		schoolUrl:U("public/Account/getSchoolList"),
		tabs:new Array('ABCDEF','GHIJ','KLMN','PQRSTUVW','XYZ')
}
var area_cache=new Map();
$(function(){
	$('.selectArea').click(function(){
		var _this=$(this);
		var url=opt.areaUrl;
		var param=null;
		var key=null;
		if(isSchool(_this)){
			url=opt.schoolUrl;
			var provId=$('input[name=provId]').val();
			var cityId=$('input[name=cityId]').val();
			var areaId=$('input[name=areaId]').val();		
			if(provId==0&&cityId==0&&areaId==0){
				return false;
			}
			param={provId:provId,cityId:cityId,areaId:areaId};
			key=provId+'_'+cityId+'_'+areaId;
		}else{
			var pid=getPid(_this);
			if(pid<0){
				return false;
			}		
			param={pid:pid};
			key=pid;
		}
		var _div=displayDiv(_this.parent());
		var data=area_cache.get(key);
		if(data!=null){
			processData(_div,data,_this);
		}else{
			$.get(url,param,function(res){
				var data=eval('('+res.data+')');
				area_cache.put(key,data);
				processData(_div,data,_this);
			},'json')
		}
	})
})
function getPid(obj){
	var _id=obj.attr('id');
	if(_id=='provName'){
		return '0';
	}else if(_id=='cityName'){
		var val=$('input[name=provId]').val();
		return val<=0?-1:val;
	}else if(_id=='areaName'){
		var val=$('input[name=cityId]').val();
		return val<=0?-1:val;
	}
}
function displayDiv(container){
	$('.select-city-wrap a.close').click();
	var _div=$('<div class="select-city-wrap"><a class="close" href="javascript:;"><i class="icons close"></i></a><div class="title"></div><div class="sc-main"><div class="loading"><img src="'+THEME_URL+'/images/loading.gif" alt="loading"></div></div></div>');
	for(var i in opt.tabs){
		$('.title',_div).append('<a class="sc-part" href="javascript:;">'+opt.tabs[i]+'</a>');
	}
	//关闭按钮
	_div.find('a.close').click(function(event) {
		_div.hide(100,function(){
			_div.remove();
		});
		$('object').css('visibility','visible');
	});

	_div.find('.loading').show();
	$('object').css('visibility','hidden');
	container.append(_div);
	return _div;
}
function processData(_div,data,_obj){
	var divArr=new Array();
	for(var i in opt.tabs){
		divArr.push($('<div class="sc-main-part"></div>'));
	}
	$.each(data,function(index,data){
		var tabIndex=getTabIndex(index);
		str = '<p><span>'+index+'</span>';
		for(i in data){
			str = str + '<a href="javascript:;" date="'+data[i].id+'">'+ data[i].name + '</a>';
		}
		str += '</p>';
		divArr[tabIndex].append(str);
	})
	_div.find('.sc-main').append(divArr);
	_div.find('.title .sc-part:eq(0)').addClass('active');
	_div.find('.loading').hide();
	_div.find('.sc-main-part:eq(0)').show();
	
	//头部hover状态
	_div.find('.title .sc-part').hover(function() {
		var index = $(this).index();
		$(this).parents('.title').find('.sc-part.active').removeClass('active');
		$(this).addClass('active');
		_div.find('.sc-main-part').hide();
		_div.find('.sc-main-part').eq(index).show();
	});
	//选择值
	_div.find('.sc-main').click(function(event) {
		var _this = $(event.target);
		if(_this.is('a') && !_this.hasClass('.sc-part')){
			_obj.text(_this.text()).attr('title',_this.text());
			var _input=_obj.siblings('input[type=hidden]');
			if(_input.val()!=_this.attr('date')){
				clearData(_obj);
			}
			_input.val(_this.attr('date'));
			_div.hide(100,function(){
				_div.remove();
				$('object').css('visibility','visible');
			});
		}
	});
}
function getTabIndex(letter){
	for(var i in opt.tabs){
		var title=opt.tabs[i];
		if(title.indexOf(letter)>-1){
			return i;
		}
	}
	return 0;
}
function isSchool(obj){
	return obj.attr('id')=='schoolName';
}
function clearData(obj){
	var _id=obj.attr('id');
	if(_id=='provName'){
		$('input[name=cityId]').val(0);
		$('#cityName').text('市');
		$('input[name=areaId]').val(0);
		$('#areaName').text('区');
		$('input[name=schoolId]').val(0);
		$('#schoolName').text('学校');
	}else if(_id=='cityName'){
		$('input[name=areaId]').val(0);
		$('#areaName').text('区');
		$('input[name=schoolId]').val(0);
		$('#schoolName').text('学校');
	}else if(_id=='areaName'){
		$('input[name=schoolId]').val(0);
		$('#schoolName').text('学校');
	}
}