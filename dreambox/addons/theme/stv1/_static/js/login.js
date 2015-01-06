$(function() {
	// login
	$('.toLogin').click(function() {
		//检测placeholder
		$('#login_dialog .input-group input').each(function(index, el) {
			if($(this).val() !='' && $(this).val() != null){
				$(this).parent().find('.placeholder').remove();
			}
		});
		$('#login_dialog').fadeIn('normal');
		eleCenter($('#login_dialog .dialog-box'));
		
	});
	$('.dialog .input-group-control').focus(function() {
		$(this).parent().removeClass('error');
	});
	$('#login_dialog form').submit(function() {
		var _this = $(this);
		if(_this.data('logining')){
			return false;
		}
		_this.data('logining','1');
		$('#login_dialog .error').removeClass('error');
		var uname = $('#login_dialog .uname').val();
		var pass = $('#login_dialog .pass').val();
		if (uname.length == 0) {
			$('#login_dialog .ud .err-text').text('请输入账号');
			$('#login_dialog .ud').addClass('error');
			_this.removeData('logining');
		} else if (pass.length == 0) {
			$('#login_dialog .pd .err-text').text('请输入密码');
			$('#login_dialog .pd').addClass('error');
			_this.removeData('logining');
		} else {
			var remember = $('#login_dialog .checkbox input').val();
			$('#login_dialog .login-submit').val('登    录    中......');
			$.post(_this.attr('action'), {
				login_email : uname,
				login_password : pass,
				login_remember : remember
			}, function(d) {
				if (d.status == 1) {
					$('#login_dialog').hide();
					location.reload();
				} else {
					$('#login_dialog .ud .err-text').text(d.info);
					$('#login_dialog .ud').addClass('error');
				}
				$('#login_dialog .login-submit').val('登    录');
				_this.removeData('logining');
			}, 'json');
		}
		return false;
	});
	// forget
	$('.forget').click(function() {
		$('.dialog').fadeOut(0);
		$('#forget_dialog').fadeIn('normal');
		eleCenter($('#forget_dialog .dialog-box'));
	});
	$('#forget_dialog form').submit(function() {
		var _this = $(this);
		if(_this.data('doing')){
			return false;
		}
		_this.data('doing','1');
		$('#forget_dialog .login-submit').val('处理中...');
		$.post(_this.attr('action'), {
			email : _this.find('input[name=email]').val()
		}, function(res) {
			if (res.status == 1) {
				$('#forget_dialog').fadeOut();
				showFindSuccess();
			} else {
				_this.find('.err-text').text(res.info);
				_this.find('.input-group').addClass('error');
			}
			$('#forget_dialog .login-submit').val('找回密码');
			_this.removeData('doing');
		}, 'json');
		return false;
	})
	$('.login-reg').click(function() {
		$('.dialog').hide(0);
		$('.toReg').click();
	});
	// reg
	$('.toReg').click(function() {
		//检测placeholder
		$('#reg_dialog .input-group input').each(function(index, el) {
			if($(this).val() !='' && $(this).val() != null){
				$(this).parent().find('.placeholder').remove();
			}
		});
		$('#reg_dialog').fadeIn('normal');
		eleCenter($('#reg_dialog .dialog-box'));
	});
	$('#reg_dialog .login-submit').click(
			function() {
				var _this = $(this);
				if(_this.data('reging')){
					return false;
				}
				if($('input[name=protocol]').length && !$('input[name=protocol]').is(':checked')){
					$('#perror').show();
					return false;
				}
				$('#perror').hide();
				_this.data('reging','1');
				$('#reg_dialog .error').removeClass('error');
				$('#reg_dialog input[name=utype]').val(
						$('#reg_dialog .select').attr('date-value'));
				var ser = $('#reg_dialog form').serialize();
				$('#reg_dialog .login-submit').val('正在加入...');
				$.post(U('public/Register/doStep1'), ser, function(res) {
					var data = res.data;
					if (res.status == '1') {
						$('#reg_dialog form input[name=uid]').remove();
						showActive(data.uid);
					} else {
						if ('undefined' != typeof (data.email)) {
							setError($('#reg_dialog input[name=email]'),
									data.email);
						}
						if ('undefined' != typeof (data.uname)) {
							setError($('#reg_dialog input[name=uname]'),
									data.uname);
						}
						if ('undefined' != typeof (data.password)) {
							setError($('#reg_dialog input[name=password]'),
									data.password);
						}
					}
					$('#reg_dialog .login-submit').val('立即加入');
					_this.removeData('reging');
				}, 'json')
			})

})

var initTime = 59;
var options = {
	time : initTime,
	uid : 0,
	email : '',
	timeout : 0
}
function showActive(uid) {
	options.time = initTime;
	options.uid = uid;
	options.email = $("#reg_dialog input[name=email]").val();
	$('.dialog').hide();
	if ($('#reg_active').length > 0) {
		$('#reg_active').remove();
	}
	$dialog = $("<div class='dialog' id='reg_active'><div class='overlayer'></div><div class='dialog-box regsucc'><h1 class='title'>注册<a class='close fr' href='javascript:;'><i class='icons close'></i></a></h1><div class='dialog-main'><div class='row center'><img src='"
			+ THEME_URL
			+ "/images/pic2.png'></div><div class='row center'><h4>恭喜您，已成功开通账号</h4><h4>激活邮件已发送至"
			+ options.email
			+ "，请立即登录邮箱<a class='cblue' target='_blank' href='"
			+ U('public/Register/toLoginEmail', new Array([ 'email='
					+ options.email ]))
			+ "'>激活账号</a></h4><p>若长时间未收到邮件，可点击“<a class='cblue resend' href='"
			+ U('public/Register/resendActivationEmail')
			+ "&uid="
			+ options.uid
			+ "'>重新发送</a>”或“<a class='cblue rereg' href='javascript:'>重新注册</a>”</p></div></div></div></div>");
	$dialog.find('.row p .resend').click(function() {
		resend($(this));
		return false;
	});
	$dialog.find('.row p .rereg').click(
			function() {
				$dialog.fadeOut();
				$('#reg_dialog form')
						.append(
								"<input type='hidden' name='uid' value='" + uid
										+ "'/>");
				$('.toReg').click();
				clearInterval(options.timeout);
			})
	$dialog.find('.close').click(function() {
		$dialog.fadeOut();
		clearInterval(options.timeout);
	});
	$('#header').append($dialog);
	eleCenter($('.dialog-box',$dialog));
	$dialog.fadeIn('normal');
	startInterval($dialog.find('.row p .resend'));
}
function showFindSuccess() {
	options.time = initTime;
	options.email = $("#forget_dialog input[name=email]").val();
	if ($('#findsuc').length > 0) {
		$('#findsuc').remove();
	}
	$dialog = $("<div class='dialog' id='findsuc'><div class='overlayer'></div><div class='dialog-box fp' style='top:-100000px'><h1 class='title'>找回密码<a class='close fr' href='javascript:;'><i class='icons close'></i></a></h1><div class='dialog-main'><div class='row center'><img src='"
			+ THEME_URL
			+ "/images/success.png'></div><div class='row'><h5>邮件已成功发送至"+options.email+"，请注意查收。 <a class='cblue' target='_blank' href='"
			+ U('public/Register/toLoginEmail', new Array([ 'email='
					+ options.email ]))
			+ "'> 接收邮件</a>！</h5><p class='mt30'>请您注意接收邮件，并按照邮件中的提示操作，完成安全验证。没有收到邮件？ <a class='cblue resend' href='"
			+ U('public/Passport/doFindPasswordByEmailAjax', new Array(
					[ 'email=' + options.email ]))
			+ "'>重新发送</a></p></div></div></div></div>");
	$dialog.find('.row p .resend').click(function() {
		resend($(this));
		return false;
	});
	$dialog.find('.close').click(function() {
		$dialog.fadeOut();
		clearInterval(options.timeout);
	});
//	$dialog.hide();
	$('#header').append($dialog);
	$dialog.fadeIn('normal');
	eleCenter($('.dialog-box',$dialog));
	startInterval($dialog.find('.row .resend'));
}
function resend(obj) {
	$.get(obj.attr('href'));
	startInterval(obj);
}
function startInterval(obj) {
	obj.removeClass('cblue').addClass('cgrey').unbind('click').text(
			'重新发送' + options.time).click(function() {
		return false;
	});
	options.timeout = setInterval(function() {
		interval(obj);
	}, 1000);
}
function interval(obj) {
	options.time--;
	if (options.time == 0) {
		clearInterval(options.timeout);
		obj.removeClass('cgrey').addClass('cblue').text('重新发送').click(
				function() {
					options.time = initTime;
					resend(obj);
					return false;
				});
	} else {
		obj.text('重新发送' + options.time);
	}
}
function setError(obj, errorInfo) {
	obj.parent().addClass('error');
	obj.siblings('.err-text').text(errorInfo);
}