/**
 * 相册核心Js对象
 * 
 * @author zivss <guolee226@gmail.com>
 * @version TS3.0
 */
var photo = {};
/**
 * 编辑图片弹窗
 * 
 * @param integer
 *            albumId 相册ID
 * @param integer
 *            photoId 图片ID
 * @return void
 */

var opt={
	textMax:140,
	uid:0,
	photo_id:0,
	mid:0,
	mavatar:''
}
$(function(){
	$('#reply').click(function(){
		var _this=$(this);
		if(_this.data('doing')){
			return false;
		}
		_this.data('doing','1');
		if(opt.uid<=0){
			$('.header-main .toLogin').click();
			return false;
		}
		var text=$("#comment").val();
		if(checkComment(text)){
			$('.person-box-r .loading').show(100);
			_this.css('width','80px').text('评论中...');
			$.post(U('photo/Index/doComment'),{uid:opt.uid,photo_id:opt.photo_id,comment:text},function(res){
				if(res.status==1){
					dreambox.dialog('提示','评论成功！');
					res.data.comment=text;
					var e=process(res.data);
					$('#c_list').prepend(e);
					$("#comment").val('');
				}else{
					dreambox.alert(res.info?res.info:'评论失败',null,2);
				}
				_this.css('width','50px').text('评论');
				_this.removeData('doing');
				$('.person-box-r .loading').hide(100);
			},'json')
		}else{
			_this.removeData('doing');
		}
	});	
	$('#comment').bind('propertychange input',function(){
		var text=$(this).val();
		var num=opt.textMax-text.length;
		if(num>0){
			$('#text_num').text('可以输入'+num+'个字');
		}else{
			$('#text_num').text('已超过'+Math.abs(num)+'个字');
		}
	});
})
photo.init=function(uid,photo_id){
	$('.person-box-r .loading').show(100);
	opt.uid=MID;
	opt.photo_id=photo_id;
	opt.mid=uid;
	if(MID){
		opt.mname=$('.header-main .header-msg a.teacher-name').text();
		opt.mavatar=$('.header-main .header-msg a.avatar img').attr('src');
	}
	$.get(U('photo/Index/getComments'),{photo_id:opt.photo_id},function(res){
		if(res.status==1){
			for(var i in res.data){
				var e=process(res.data[i]);	
				$('#c_list').append(e);
			}
		}
		$('.person-box-r .loading').hide(100);
	},'json')
}

photo.editphotoTab = function(albumId, photoId) {
	var $dialog = $("<div class='dialog createPhoto' style='display: none;'> <div class='overlayer' ></div> <div class='dialog-box' > <h1 class='title'>编辑照片</h1> <div class='dialog-main'> <div class='row'><label>照片名称</label><input type='text' id='pname' class='input'></div> <div class='row'> <label>所属相册</label> <div class='selectGroup'> <div class='select' id='ownerAlbum' date-value='0'><label class='text'></label><em class='bottomdirection'></em></div> <ul class='select-items'></ul> </div>  </div></div><div class='dialog-footer'> <p><input class='btn footer-cancel' type='button' value='取消'><input class='btn footer-submit fr' type='submit' value='确定'></p> </div></div>");
	// select click
	$('.selectGroup', $dialog).click(function(event) {
		var _this = $(event.target);
		if(_this.is('.select') || _this.is('label') || _this.is('em')){
			_this = $(this).find('.select');
			_this.next().toggle();
			_this.find('em').toggleClass('bottomdirection').toggleClass('topdirection');
		}else if(_this.is('li')){
			_this.parent().find('li.active').removeClass('active');
			_this.addClass('active');
			_this.parents('.selectGroup').find('.select').attr('date-value',_this.attr('date-value'))
			                                             .find('.text').text(_this.text());
			_this.parent().hide();
			$(this).find('em').toggleClass('bottomdirection').toggleClass('topdirection');
		}
	});
	$.get(U('photo/Manage/getAlbums'), null, function(res) {
		var _ul = $('.select-items', $dialog);
		var count = 0;
		for ( var i in res) {
			var _li = $("<li date-value='" + res[i].id + "'>" + res[i].name
					+ "</li>");
			_ul.append(_li);
			count++;
		}
		if (count > 0) {
			$('.selectGroup li[date-value=' + albumId + ']', $dialog).click();
		}
	}, 'json');
	$('#pname',$dialog).val($('#photoName').val());
	$('.footer-submit', $dialog).click(
			function() {
				var id = photoId;
				var name = $('#pname',$dialog).val();
				var albumIdNew = $('#ownerAlbum').attr('date-value');
				if (!name || getLength(name.replace(/\s+/g, "")) == 0) {
					$dialog.remove();
					dreambox.alert('图片名字不能为空！', null, 3);
					return false;
				}else if(name.length>15){
					$dialog.remove();
					dreambox.alert('名字长度不能超过15个字！', null, 3);
					return false;
				}
				$.post(U('photo/Manage/do_update_photo'), {
					id : id,
					name : name,
					albumId : albumIdNew
				}, function(data) {
					$dialog.remove();
					if (data.result == 1) {
						dreambox.dialog('提示', '修改成功！', function() {
							location.href = U('photo/Index/photo') + '&id='
									+ id + '&aid=' + albumIdNew;
						});
					} else {
						dreambox.alert('图片信息无变化！', null, 3);
					}
				}, 'json');
			});
	$('.footer-cancel', $dialog).click(function(){
		$dialog.remove();
	});
	$('body').append($dialog);
	$dialog.fadeIn(400);
	eleCenter($dialog);
};
/**
 * 删除单张图片
 * 
 * @param integer
 *            albumId 相册ID
 * @param integer
 *            photoId 图片ID
 * @return void
 */
photo.delphoto = function(albumId, photoId) {
	dreambox.confirm('你确定要删除这张照么？', null, '删除照片', function() {
		$.post(U('photo/Manage/delete_photo'), {
			id : photoId,
			albumId : albumId
		}, function(data) {
			if (data == 1) {
				dreambox.dialog('提示', '删除成功！', function() {
					location.href = U('photo/Index/album') + '&id=' + albumId
							+ '#mao_ts'
				});
				return false;
			} else {
				dreambox.alert('删除失败！', null, 3);
			}
		});

	});
};
/**
 * 设置封面操作
 * 
 * @param integer
 *            albumId 相册ID
 * @param integer
 *            photoId 图片ID
 * @return void
 */
photo.setcover = function(albumId, photoId) {
	dreambox.confirm('你要将这张图片设置为封面么？', null, '设置封面', function() {
		$.post(U('photo/Manage/set_cover'), {
			photoId : photoId,
			albumId : albumId
		}, function(data) {
			if (data == 1) {
				dreambox.dialog('提示', '封面设置成功！');
			} else if (data == -1) {
				dreambox.alert('该图片不存在！', null, 3);
			} else {
				dreambox.alert('当前封面已是该图片，或设置失败！', null, 3);
			}
		});
	})
};
function replay(obj){
	var name=$(obj).siblings('.maincon').find('.hfxm a').text();
	var title='回复@' + name + ' ：';
	$('#comment').val(title);
}
function process(data){
	var _li=$('<li> <a href="javascript:;" class="avatar tiny"><img alt="头像"></a> <div class="maincon"> <span class="hfxm"><a href="javascript:;" class="c_name"></a></span> <span class="hfnr commont"></span> </div> <label class="isaycon-time c_time"></label> <a class="a-reply" onclick="replay(this)" href="javascript:;">回复</a> </li>');
	var href=U('public/Profile/index')+"&uid="+data.uid;
	_li.find('img').attr('src',data.avatar).attr('title',data.uname);
	_li.find('.c_name').attr('href',href).text(data.uname);
	_li.find('.commont').text(data.comment);
	_li.find('.c_time').text(data.ctime);
	return _li;
}
function checkComment(text){
	if(!text||text.length<=0){
		dreambox.alert('请输入内容',null,2);
		return false;
	}else if(text.length>140){
		dreambox.alert('内容过长，不能超过140个字',null,2);
		return false;
	}
	return true;
}
