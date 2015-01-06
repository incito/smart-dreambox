$(function() {
	$(document).on('click', function(event) {
		if ($(event.target).is(':not(a.cblue,.dialog-admin,.dialog-admin *)')) {
			$('.dialog-admin').hide();
		}
	});
	$('.close').click(
			function(event) {
				/* Act on the event */
				var _li = $(this).closest('li');
				if (_li.find('span:first').attr('title') == $('.termInfo-span')
						.text()) {
					alert('被绑定的管理员不能删除');
					return;
				}
				var uid = _li.find('span:first').attr('user_id') + ",";
				delIds += uid;
				_li.remove();
			});
	$('input[name=stime]').datepick({
		dateFormat : 'yy-mm-dd',
		changeYear : true,
		yearRange : '-80:+10',
	});
	$('input[name=etime]').datepick({
		dateFormat : 'yy-mm-dd',
		changeYear : true,
		yearRange : '-80:+10',
	});
	$('.btn.footer-cancel').click(function() {
		commit(0);
	});
	$('.btn.footer-submit').click(function() {
		commit(1);
	});
	$('input[name=teacher_num]').keyup(function() {
		replaceChar($(this));
	});
	$('input[name=class_num]').keyup(function() {
		replaceChar($(this));
	});
	$('input[name=student_num]').keyup(function() {
		replaceChar($(this));
	});
	$('input[name=phone]').keyup(function() {
		replaceChar($(this));
	});

	// 重新绑定
	$('a.cblue').click(function() {
		$('.dialog-admin').show();
	});
	$('.dialog-admin li a').click(function() {
		$('.termInfo-span').text($(this).text());
		$('.termInfo-span').attr('admin', $(this).attr('admin'));
		$(this).parent().parent().parent().hide();
	});
	$('input.btn.fl').click(function() {
		$('#inner_box').empty();
	});
})
var delIds = "";
function check() {
	var pattern = /^[1-9][0-9]*$/;
	if ($('input[name=stime]').val().length == 0) {
		dreambox.alert('开始时间不能为空');
		return false;
	} else if ($('input[name=etime]').val().length == 0) {
		dreambox.alert('结束时间不能为空');
		return false;
	} else if (!pattern.test($('input[name=teacher_num]').val())) {
		dreambox.alert('请输入学校教师总数并且必须为正整数');
		return false;
	} else if (!pattern.test($('input[name=class_num]').val())) {
		dreambox.alert('请输入学校班级总数并且必须为正整数');
		return false;
	} else if (!pattern.test($('input[name=student_num]').val())) {
		dreambox.alert('请输入学校学生总数并且必须为正整数');
		return false;
	} else {
		var stime = new Date(($("input[name=stime]").val()).replace(/-/g, "/"));
		var etime = new Date(($("input[name=etime]").val()).replace(/-/g, "/"));
		var myDate = new Date();
		var fullYear = myDate.getFullYear();
		var mounth = myDate.getMonth() + 1;
		var day = myDate.getDate();
		mounth = mounth < 10 ? ("0" + mounth) : mounth;
		day = day < 10 ? ("0" + day) : day;
		var time = new Date(fullYear + "/" + mounth + "/" + day);
		if (stime > etime) {
			dreambox.alert('开始时间不能大于结束时间');
			return false;
		} else if (etime < time) {
			dreambox.alert('结束时间不能晚于当前时间');
			return false;
		}
	}
	return true;
}

function commit(status) {
	if (check()) {
		$names = $('li .name');
		var data = {
			'stime' : trim($('input[name=stime]').val()),
			'etime' : trim($('input[name=etime]').val()),
			'teacher_num' : trim($('input[name=teacher_num]').val()),
			'class_num' : trim($('input[name=class_num]').val()),
			'student_num' : trim($('input[name=student_num]').val()),
			'schoolMaster' : trim($('input[name=schoolMaster]').val()),
			'admin' : trim($('.termInfo-span').attr('admin')),
			'phone' : trim($('input[name=phone]').val()),
			'status' : status,
			'ids' : delIds
		}
		$.post(U('dreambox/Term/operateTerm'), data, function(res) {
			if (res.indexOf("showCourse") != -1 || res.indexOf("index") != -1) {
				// 学校主页
				if ($('#term').length > 0) {
					dreambox.dialog('修改学期', '保存成功', function() {
						location.reload(true);
					});
				} else {
					window.location.href = U(res)+'&uid='+$('#uid').val();
				}
			} else {
				dreambox.alert(res);
			}
		});
	}
}