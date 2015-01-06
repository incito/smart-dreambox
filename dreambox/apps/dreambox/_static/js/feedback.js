$(function(){
	//居中
//	eleCenter('.signup');
	var _signup = $('.dialog-box.signup');
	//绑定日历控件
//	var startDate = $("#minDate").val().split(",");
//	var endDate = $("#maxDate").val().split(",");
//	$('.date_time').datepick({dateFormat: 'yy-mm-dd', showButtonPanel: false, clearText:'', minDate: new Date(startDate[0], startDate[1] - 1, startDate[2]), maxDate: new Date(endDate[0], endDate[1] - 1, endDate[2])});	
	
	//班级输入框的数据校验
	$("input[name='class_num']").keyup(function(event){
		var _this = $(event.target);
		if(isNaN(_this.val())){
			_this.val("");
		}
	});
	
	//select click
	_signup.find('.selectGroup').click(function(event) {
		var _this = $(event.target);
		//判断是否禁用
		if($(this).is('.disabled')){
			return;
		}
		
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
			
			//启用提交按钮
			
		}
	});
	//
	_signup.find('.su-course-ul').click(function(event) {
		var _this =  $(event.target);
		if(_this.is('.edit')){//click edit	
			
			_this.parents('.ft-info').hide();
			_this.parents('li').find('.sc-info').show();
			
		}else if(_this.is('.del')){//click del

			dreambox.confirm("","您确定这节课没上吗?","确认删除",function(){
				var li = _this.parents('li');
				var sc_id = li.find("input[name='sc_id']").val();
				var data = "id="+sc_id;
				$.post(U('dreambox/LessonFeedback/removeCourse'), data, function(data){
						_this.parents('li').remove();
						//更新签到条数
						var _em = $('#qiandao em');
						var _count = parseInt(_em.text()) - 1;
						refreshSignCount(_em,_count);
				   }
				);
			});
			
		}else if(_this.is('.ok')){

			var li = _this.parents('li');
			var _class = li.find("input[name='class_num']");
			//判断班级是否为空
			var class_num = _class.val();
			if(class_num == null || class_num == ""){
				if(!_class.parent().is(".error")){
					_class.parent().toggleClass('error');
				}
				return;
			}
			
			var sc_id = li.find("input[name='sc_id']").val();
			var time = li.find("input[name='lesson_time']").val();
			var grade_id = li.find("ul[name='grade']").find(".active").attr("date-value");
			var section_id = li.find("ul[name='section']").find(".active").attr("date-value");
			var course_id = li.find("ul[name='course']").find(".active").attr("date-value");
			
			var _ft = _this.parents('li').find('.ft-info'),
			    _sc = _this.parent();

			var data = "id="+sc_id+"&time="+time+"&grade="+grade_id+"&class="+class_num+"&section="+section_id+"&course="+course_id;
			$.post(U('dreambox/LessonFeedback/updateCourse'), data, function(res){
					//校验是否冲突
					if(res.data.status == 0){
						dreambox.alert(res.data.msg);
						return;
					}
					var date = _sc.find('.s-time input').val();
					_ft.find('.l-time').text(date + "（"+ res.data.day_of_week +"）");
					_ft.find('.l-class').text(_sc.find('.s-grade .text').text()+_sc.find('.s-class input').val()+"班");
					_ft.find('.l-lesson').text(_sc.find('.s-lesson .text').text());
					_ft.find('.l-course').text(_sc.find('.s-course .text').text());
					_ft.find(".selectGroup").find('.select').attr('date-value',0).find(".text").text("选择课时");
					
					//选择时间大于当前时间
					if(res.data.gt_now == 1){
						_ft.find(".select-items").html("");
						_ft.find(".selectGroup").toggleClass('disabled');
						_ft.find(".selectGroup").find('label').attr('title', '上课时间还没到，您还不能选择课时！');
					}else{
						var html = "";
						var hours = res.data.hours;
						for(var i in hours){
							var hour = hours[i];
							html += '<li date-value='+hour.number+'>'+hour.title+'</li>';
						}
						_ft.find(".select-items").html(html);
						_ft.find(".selectGroup").removeClass('disabled');
						_ft.find(".selectGroup").find('label').attr('title', '');
					}
					
					_sc.hide();
					_ft.show();
			   },'json'
			);
		}else if(_this.is('.cancel')){
			var _ft = _this.parents('li').find('.ft-info'),
				_sc = _this.parent();
			_sc.hide();
			_ft.show();
		}
	});

	var submitting = false;
	//提交签到
	_signup.find('.footer-submit').click(function(){
		//如果正在提交，则返回
		if(submitting){
			return false;
		}
		
		//校验提交按钮是否可用
//		if($("a.submit").find()){
//			
//		}
		
		//检测是否处于编辑状态
		var isEdit = false;
		$(".su-course-ul").find(".sc-info").each(function(){
			if($(this).is(":visible")){
				isEdit = true;
				return false;
			}
		});
		if(isEdit){
			dreambox.alert("您还处于编辑状态，请先确认修改！");
			return false;
		}
		
		//
		var count = 0;
		var param = new Array();
		$(".ft-info").each(function(){
			var hour_id = $(this).find(".selectGroup").find('.select').attr('date-value');
			//未选择课程
			if(hour_id == 0){
				return;
			}
			
			param[count] = {
				sc_id : $(this).find("input[name='sc_id']").val(),
				term_id : $(this).find("input[name='term_id']").val(),
				course_name : $(this).find('.l-course').text(),
				grade_name :  $(this).parent().find('.s-grade').find('.text').text(),
				class_name :  $(this).parent().find('.s-class').find('input').val(),
				section_num : $(this).find('.l-lesson').text(),
				hours_name : $(this).find('.selectGroup .text').text(),				
				time : $(this).find('.l-time').text()
			}
			count++;
		});
		if(count == 0){
			dreambox.alert("请先选择课时！");
			return false;
		}
		
		submitting = true;
		data = {
			"uid": $("#uid").val(),
			"jsonarray": JSON.stringify(eval(param))
		}
		$.post(U('dreambox/LessonFeedback/feedback'), data, function(res){
			if(res == -1){
				dreambox.alert("其他账号已经登陆，请刷新重试！", function(){
					//关闭窗口
					signup.close();
				});
			}else if(res.data.failed_count > 0){
				var _em = $('#qiandao em');
				var _count = parseInt(_em.text()) - parseInt(res.data.success_count);
				refreshSignCount(_em,_count);
				dreambox.alert(res.data.failed_count + "条记录签到失败！\n" + res.data.error, function(){
					//关闭窗口
					signup.close();
				});
			}else if(res.data.failed_count == 0){
				var _em = $('#qiandao em');
				var _count = parseInt(_em.text()) - parseInt(res.data.success_count);
				refreshSignCount(_em,_count);
				//关闭窗口
				signup.close();
				$dialog=$("<div class='dialog  integralDialog' style='display:none;'> <div class='overlayer'></div> <div class='dialog-box' style='display: block;'> <h1 class='title'><a class='close fr' href='javascript:;'><i class='icons close'></i></a></h1> <div class='dialog-main'> <img src='"+THEME_URL+"/images/pic6.png' alt='恭喜' title='恭喜'> <h6>恭喜你</h6> <p>本次提交签到获得 <strong class='get_score'></strong> 积分</p></div> </div> </div> ");
				$('body').append($dialog);
				$dialog.find('.get_score').text(res.data.integral);
				$dialog.find('a.close').click(function(){
					$dialog.fadeOut('fast',function(){
						$dialog.remove();
					})
				});
				$dialog.fadeIn('fast');
				eleCenter($dialog.find('.dialog-box'));
				setTimeout(function(){
					$dialog.fadeOut('normal',function(){
						$dialog.remove();
					})
				},3000);
			}
			submitting =false;
		},'json');
	});

	//下次再签到
	_signup.find('.footer-cancel').click(function(){
		var count = 0;
		$(".ft-info").each(function(){
			var hour_id = $(this).find(".selectGroup").find('.select').attr('date-value');
			//未选择课程
			if(hour_id == 0){
				return;
			}
			count++;
		});
		
		if(count > 0){
			dreambox.confirm("", "您已经选择了签到课时,确定不提交吗?", "确认取消", function(){
				signup.close();
				$.get(U('dreambox/LessonFeedback/nextFeedback'));
			});
		} else {
			signup.close();
			$.get(U('dreambox/LessonFeedback/nextFeedback'));
		}
	})

});
var signup = {}
signup.close = function(){
	$('.overlayer').last().remove();
	$('.dialog-box').last().remove();
}

function refreshSignCount(_em,_count){
	if(_count > 0){
		_em.text(_count);
		_em.show();
	}else{
		_em.hide();
	}
}