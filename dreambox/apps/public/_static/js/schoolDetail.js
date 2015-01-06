var cache=new Array();
$(function(){
	$(document).on('click',function(event){
		if($(event.target).is(':not(.cxbd,.dialog-admin,.dialog-admin *)')){
			$('.dialog-admin').hide();
		}
	})
	$('#toEdit').click(function(){
		$('#input-info').hide();
		$('#edit-info').show();
	});
	$('#saveInfo').click(function(){
		var _this=$(this);
		_this.text('保存中...');
		var param=$('#edit-info form').serialize();
		param+="&cid0="+$('div.cid0').attr('date-value');
		param+="&school_type="+$('div.school_type').attr('date-value');
		param+="&educational_type="+$('div.educational_type').attr('date-value');
		param+="&admin_id="+$('input[name=admin_id]').val();
		$.post(U('public/School/save'),param,function(res){
			_this.text('保存');
			if(res.status==1){
				dreambox.dialog('提示',res.info,function(){
					window.location.reload();
				});
			}else{
				dreambox.alert(res.info,null,3);
			}
		},'json');
	});
	$('#edit-info .photo-change').click(function(){
		var photo_div=$('#school_photo'); 
		photo_div.fadeIn(400);
		eleCenter($('.dialog-box',photo_div));
	});
	$('#school_photo .close').click(function(){
		$('.photoset-cancel').click();
	});
	$('#edit-info #instruction').bind('propertychange input',function(){
		var text=$(this).val();
		var maxNum=100;
		var num=maxNum-text.length;
		if(num<0){
			text=text.substr(0,maxNum);
			$(this).val(text);
			num=0;
		}
		$('#text_num').text(num);
	});
	$('#edit-info .cxbd').click(function(){
		if($('.dialog-admin').is(':hidden')){
			$('.dialog-admin').show();
			if(cache.length>0){
				processAdminList(cache);
			}else{
				$.get(U('public/School/getAdminList'),{sid:$('input[name=id]').val()},function(res){
					processAdminList(res.data);
					cache=res.data;
				},'json')
			}
		}else{
			$('.dialog-admin').hide();
		}
	})
})
function processAdminList(data){
	$('.dialog-admin ul').empty();
	for(var i in data){
		$('.dialog-admin ul').append("<li data='"+data[i].id+"'><a href='javascript:;'>"+data[i].name+"</a></li>");
	}
	$('.dialog-admin ul li').click(function(){
		var admin_input=$('input[name=admin_id]');
		if(admin_input.length==0){
			admin_input=$("<input type='hidden' name='admin_id'/>")
			$('.dialog-admin ul').prepend(admin_input);
		}
		admin_input.val($(this).attr('data'));
		$('#edit-info .admin_name').text($(this).text());
		$('#edit-info .cxbd').click();
	});
}
function checkParam(){
	
}