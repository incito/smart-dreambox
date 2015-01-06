function create_update_album(aid, aname, privacy) {
	var $dialog = buildCreateAlbum(aid, aname);
	$('.footer-submit', $dialog).click(function() {
		if (aid) {
			do_update_album(aid, $dialog);
		} else {
			do_create_album(1, $dialog);
		}
	});
	$('.footer-cancel', $dialog).click(function() {
		$dialog.remove();
	});
	$('.delAlbum', $dialog).click(function() {
		$dialog.remove();
		$('.del').click();
	});
	// select click
	$('.selectGroup', $dialog)
			.click(
					function(event) {
						var _this = $(event.target);
						if (_this.is('.select') || _this.is('label')
								|| _this.is('em')) {
							_this = $(this).find('.select');
							_this.next().toggle();
							_this.find('em').toggleClass('bottomdirection')
									.toggleClass('topdirection');
						} else if (_this.is('li')) {
							_this.parent().find('li.active').removeClass(
									'active');
							_this.addClass('active');
							_this.parents('.selectGroup').find('.select').attr(
									'date-value', _this.attr('date-value'))
									.find('.text').text(_this.text());
							_this.parent().hide();
//							if (_this.attr('date-value') == 4) {
//								$('.passrow', $dialog).show();
//							} else {
//								$('.passrow', $dialog).hide();
//							}
							$(this).find('em').toggleClass('bottomdirection')
									.toggleClass('topdirection');
						}
					});
	if (privacy) {
		$('.selectGroup [date-value=' + privacy + ']', $dialog).click();
	}
	$('body').append($dialog);
	$dialog.fadeIn(400);
	eleCenter($dialog);
}
function do_create_album(isRefresh, obj) {
	isRefresh = (typeof isRefresh === 'undefined') ? 0 : isRefresh;
	var name = $('#aname').val().replace(/\s+/g, ""), privacy = $('#privacy')
			.attr('date-value'), password = $('#apass').val();

	if (!name) {
		obj.remove();
		dreambox.alert('名称不能为空');
		return false;
	} else if (name.length > 12) {
		obj.remove();
		dreambox.alert('名称不能超过12个字');
		return false;
	}
	$.post(U('photo/Manage/do_create_album'), {
		name : name,
		privacy : privacy,
		privacy_data : password
	}, function(res) {
		if (res.status == -1) {
			obj.remove();
			dreambox.alert(res.info);
		} else if (res.status == 1) {
			if (isRefresh) {
				location.reload();
			} else {
				parent.setAlbumOption(res.data);
			}
		} else if (res.status == 0) {
			obj.remove();
			dreambox.alert('创建失败',null,3);
		}
	}, 'json');
};
function do_update_album(aid, obj) {
	var name = $('#aname').val().replace(/\s+/g, ""), privacy = $('#privacy')
			.attr('date-value'), password = $('#apass').val();

	if (!name) {
		dreambox.alert('名称不能为空');
		return false;
	} else if (name.length > 12) {
		dreambox.alert('名称不能超过12个字');
		return false;
	}
	$.post(U('photo/Manage/do_update_album'), {
		albumId : aid,
		album_name : name,
		album_privacy : privacy,
		album_privacy_data : password
	}, function(res) {
		if (res.status == 1) {
			location.reload();
		} else {
			obj.remove();
			dreambox.alert(res.info);
		}
	}, 'json');
}
var delAlbum = function(albumId,acount) {
	var doDelAlbum = function() {
		$.post(U('photo/Manage/delete_album'), {
			id : albumId
		}, function() {
			dreambox.dialog('提示','删除成功！',function() {
				location.href = U('photo/Index/albums');
			});
		});
	};
	dreambox.confirm('确定删除该相册？相册中有'+acount+'张照片',null,'删除相册',doDelAlbum);
};
function buildCreateAlbum(aid, aname) {
	if(aid){
		return $("<div class='dialog editPhoto' style='display:none;'> <div class='overlayer' ></div> <div class='dialog-box' > <h1 class='title'>编辑相册</h1> <div class='dialog-main'> <div class='row'><label>相册名字</label><input type='text' id='aname'  class='input' "+(aname?"value='"+aname+"'":"")+"><a href='javascript:;' class='cblue delAlbum'>删除该相册</a></div> <div class='row'> <label>访问权限</label> <div class='selectGroup'>  <div class='select' id='privacy' date-value='1'><label class='text'>所有人可见</label><em class='bottomdirection'></em></div> <ul class='select-items'> <li date-value='1'>所有人可见</li> <li date-value='2'>我的粉丝可见</li> <li date-value='3'>仅自己可见</li> </ul> </div> </div>  </div> <div class='dialog-footer'> <p><input class='btn footer-cancel' type='button' value='取消'><input class='btn footer-submit fr' type='submit' value='确定'></p> </div> </div> </div>")
	}else{
		return $("<div class='dialog createPhoto' style='display: none;'> <div class='overlayer' ></div> <div class='dialog-box' > <h1 class='title'>创建相册</h1> <div class='dialog-main'> <div class='row'><label>相册名字</label><input type='text' id='aname' class='input'></div> <div class='row'> <label>访问权限</label> <div class='selectGroup'> <div class='select' id='privacy' date-value='1'><label class='text'>所有人可见</label><em class='bottomdirection'></em></div> <ul class='select-items'> <li date-value='1'>所有人可见</li> <li date-value='2'>我的粉丝可见</li> <li date-value='3'>仅自己可见</li> </ul> </div>  </div></div><div class='dialog-footer'> <p><input class='btn footer-cancel' type='button' value='取消'><input class='btn footer-submit fr' type='submit' value='确定'></p> </div></div>");
	}
}