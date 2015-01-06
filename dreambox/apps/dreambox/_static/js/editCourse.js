$(function() {
	getInitStatus();
	var eleClick;
	// select click
	$('.selectGroup')
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
							$(this).find('em').toggleClass('bottomdirection')
									.toggleClass('topdirection');
						}
					});
	// item hover
	$('.time-item').hover(function() {
		if (!$(this).hasClass('disable')) {
			$(this).addClass('hover');
		}
	}, function() {
		$(this).removeClass('hover');
	});
	// item click
	$('.time-item')
			.click(
					function(event) {
						var _this = $(this)
						if (_this.hasClass('disable'))
							return;
						if ($('.course-ul li .info-show.active').size() == 0) {
							dreambox.alert('请至少选中一门课程');
							return;
						}
						if (_this.hasClass('warning')) {
							var eleClick = _this;
							var _activeName = $('.info-show.active label:first')
									.text();
							var _gName = $('.info-show.active label:last')
									.text();
							if ((_this.attr('courseName').replace(/^\s+|\s+$/g,
									"") + _this.attr('gradeName').replace(
									/^\s+|\s+$/g, "")) == (_activeName.replace(
									/^\s+|\s+$/g, "") + _gName.replace(
									/^\s+|\s+$/g, ""))) {
								// 取消排课
								_this.removeClass('warning');
								_this.removeClass('active');
								_this.addClass('rm');
								_this.find('em').remove();
								_this.removeAttr('grade_id');
								_this.removeAttr('class_num');
								_this.attr('title', '起止时间：'
										+ _this.attr('setime') + '\n暂无课程安排信息');
								return;
							}
							// 修改消息提示
							var _info = "第" + _this.attr('id').substring(5)
									+ "周(" + _this.attr('setime')
									+ ")课程将由<span class='cblue'>"
									+ _this.attr('courseName') + "("
									+ _this.attr('gradeName')
									+ ")</span>变更为<span class='cblue'>("
									+ _activeName + _gName + ")</span>";

							dreambox
									.confirm(
											_info,
											'确定变更吗?',
											'变更课程',
											function() {
												eleClick.removeClass('warning');
												eleClick.find('em').remove();
												eleClick.addClass('active');
												eleClick
														.append('<em class="bottomrightdirection">√</em>');
												_this.attr('title', '起止时间：'
														+ _this.attr('setime')
														+ '\n课程名称：'
														+ _activeName.replace(
																/^\s+|\s+$/g,
																"")
														+ '\n班级名称：'
														+ _gName.replace(
																/^\s+|\s+$/g,
																""));
												var active = $(
														'.course-ul li.active')
														.find('.info-show');
												var course_name = active.find(
														'.l-course').attr(
														'course_name');
												var grade_id = active.find(
														'.l-class').attr(
														'grade_id');
												var class_num = active.find(
														'.l-class').attr(
														'class_num');
												_this.attr('course_id',
														course_name);
												_this
														.attr('grade_id',
																grade_id);
												_this.attr('class_num',
														class_num);
											});
						} else if (_this.hasClass('active')) {
							// 取消排课
							_this.removeClass('active');
							_this.find('em').remove();
							_this.removeAttr('grade_id');
							_this.removeAttr('class_num');
							_this.attr('title', '起止时间：' + _this.attr('setime')
									+ '\n暂无课程安排信息');
						} else {
							checkDateAndCanSelect(_this);
						}
					});

	// btn
	$('.course-items')
			.click(
					function(event) {
						var _this = $(event.target);
						if (_this.is('i.del')) {// when click del
							dreambox.confirm('删除课程后无法还原,已签到课程不会被删除', '确定要删除吗？',
									'删除课程', function() {
										delCourse(_this);
										_this.parents('li').remove();
									});
						} else if (_this.is('i.ok')) {// when click ok
							clickOk(_this);
						} else if (_this.is('.info-show')
								|| _this.is('.l-course')
								|| _this.is('.l-class')) {// when click
							// info-show
							var _infoShow = _this.parent('.info-show').size() > 0 ? _this
									.parent('.info-show')
									: _this, index = _this
									.parents('.course-ul').find('.info-show')
									.index(_infoShow);
							_infoShow.parents('.course-ul').find(
									'.info-show.active').removeClass('active');
							_infoShow.addClass('active');
						}
					});
	$('.course-ul li').click(
			function(event) {
				var _this = $(this);
				// 如果是处于编辑状态
				if ($(this).find('.info-edit:hidden').length == 0) {
					return;
				}
				changeEdit($(this));
				_this.parents('.course-ul').find('li.active').removeClass(
						'active');
				_this.addClass('active');
				// 跟所有该课程周加上边框
				var $weeks = $('.time-box  .time-item:not(.disable)');
				var course_id = _this.find('.l-course').attr('course_name');
				var grade_id = _this.find('.l-class').attr('grade_id');
				var class_num = _this.find('.l-class').attr('class_num');
				for (i = 0; i < $weeks.length; i++) {
					$week = $weeks.eq(i);
					$week.removeClass('active');
					if ($week.attr('course_id') == course_id
							&& $week.attr('grade_id') == grade_id
							&& $week.attr('class_num') == class_num) {
						$week.addClass('active');
					}
				}
			});
	$('input.footer-cancel').click(function() {
		$('#inner_box').empty();
	})
	$('.info-edit input').keyup(function() {
		var $input = $(this);
		if ($input.val().length > 3) {
			dreambox.alert('班级不能超过3位数');
		}
		$input.val($input.val().replace(/[^\d]+/, '').substring(0, 3));
	});
	$('#classInput').mouseout(function() {
		var $input = $('#classInput');
		$input.val($input.val().replace(/[^\d]+/, '').substring(0, 3));
	});
	function getInitStatus() {
		var data = {
			week_day : $('#week_day').val(),
			section_num : $('#section_num').val(),
			uid : $('#teacherId').val()
		};
		getCourseInfo(data);
	}
	function checkDateAndCanSelect(_this) {
		// 检查日期
		// 排课
		var _info = $('.course-ul li').find('.info-show.active');
		var _label = _info.find('label:last');
		var _gid = _label.attr('grade_id');
		var _cnum = _label.attr('class_num');
		var str = _this.attr('id').substring(5) + '-' + $('#week_day').val()
				+ '-' + $('#section_num').val() + '-' + _gid + '-' + _cnum
				+ ',';
		var data = {
			week_num : _this.attr('id').substring(5),
			week_day : $('#week_day').val(),
			data : str.substring(0, str.length - 1),
			uid : $('#teacherId').val()
		};
		$.post(U('dreambox/Course/checkDateAndCanSelect'), data, function(res) {
			// 如果日期合法
			if (res.status == 1) {
				_this.addClass('active');
				_this.append('<em class="bottomrightdirection">√</em>');
				var active = $('.course-ul li.active').find('.info-show');
				var course_name = active.find('.l-course').attr('course_name');
				var grade_id = active.find('.l-class').attr('grade_id');
				var class_num = active.find('.l-class').attr('class_num');
				_this.attr('course_id', course_name);
				_this.attr('grade_id', grade_id);
				_this.attr('class_num', class_num);
				_this.attr('title', '起止时间：'
						+ _this.attr('setime')
						+ '\n课程名称：'
						+ $('.info-show.active label:first').text().replace(
								/^\s+|\s+$/g, "")
						+ '\n班级名称：'
						+ $('.info-show.active label:last').text().replace(
								/^\s+|\s+$/g, ""));
			} else {
				dreambox.alert(res.info);
			}
		}, 'json');
	}
	function getCourseInfo(data) {
		$('.time-box .time-item').removeClass('active disable warning');
		$
				.post(
						U('dreambox/Course/queryWeekStatus'),
						data,
						function(res) {
							if (res.status == 1) {
								for ( var i in res.data) {
									var $week = $('#week_' + i);
									var d = res.data[i];
									var $setime = "起止时间：" + d.stime + "~"
											+ d.etime + "\n";
									var $courseName = "课程名称：" + d.course_name
											+ "\n";
									var $gradeName = "班级名称：" + d.grade_name
											+ d.class_num + "班\n";
									$week.removeClass('active disable warning');
									$week.attr("setime", d.stime + "~"
											+ d.etime);
									if (d.status == 0) {
										$week.attr("title", $setime
												+ '暂无课程安排信息');
									} else if (d.status == 1) {
										$week.addClass('warning');
										$week.attr("courseName", d.course_name);
										$week.attr("gradeName", d.grade_name
												+ d.class_num + "班");
										$week.attr("title", $setime
												+ $courseName + $gradeName);
										$week.attr("course_id", d.course_id);
										$week.attr("grade_id", d.grade_id);
										$week.attr("class_num", d.class_num);
										$week
												.append("<em class='toprightdirection'></em>");
									} else if (d.status == 2) {
										$week.addClass('disable');
										$signTime = "签到时间：" + d.sign_time
												+ "\n";
										$week.attr("title", $setime
												+ $courseName + $gradeName
												+ $signTime);
										$week
												.append("<em class='toprightdirection'></em>");
									}
								}
							} else {
								ui.box.error('获取信息失败');
							}
						}, 'json')
	}

	$('input.footer-submit')
			.click(
					function() {
						var active = $('.time-box  .time-item');
						var weekDay = $('#week_day').val() + '-';
						var sectionNum = $('#section_num').val() + '-';
						var data = '';
						for (i = 0; i < active.length; i++) {
							var _this = active.eq(i);
							if (_this.find('em.bottomrightdirection').length == 0) {
								continue;
							}
							data += ',';
							data += '2-';
							data += _this.attr('id').substring(5) + '-';
							data += weekDay;
							data += sectionNum;
							data += _this.attr('grade_id') + '-';
							data += _this.attr('class_num') + '-';
							data += _this.attr('course_id');
						}

						var disActive = $('.time-box  .time-item:not(.active,.disable,.warning)');
						for (i = 0; i < disActive.length; i++) {
							var _this = disActive.eq(i);
							if (_this.find('em.bottomrightdirection').length > 0) {
								continue;
							}
							data += ',';
							data += '1-';
							data += _this.attr('id').substring(5) + '-';
							data += weekDay;
							data += sectionNum;
							data += _this.attr('grade_id') + '-';
							data += _this.attr('class_num') + '-';
							data += _this.attr('course_id');
						}
						var uid = $('#teacherId').val();
						$
								.post(
										U('dreambox/Course/modifyCourse'),
										{
											param : data.substring(1),
											uid : $('#teacherId').val()
										},
										function(res) {
											if (res.status == 1) {
												$('#inner_box').empty();
												window.location.href = U('dreambox/Course/showCourse')+"&uid="+ uid;
											} else {
												var str = "第";
												for ( var i in res.data) {
													var _week = res.data[i];
													str += _week;
													str += ',';
													var weekId = "#week_"
															+ _week;
													// 取消排课
													$(weekId).removeClass(
															'warning');
													$(weekId).removeClass(
															'active');
													$(weekId).addClass('rm');
													$(weekId).find('em')
															.remove();
													$(weekId).removeAttr('grade_id');
													$(weekId).removeAttr('class_num');
													$(weekId)
															.attr(
																	'title',
																	'起止时间：'
																			+ _this
																					.attr('setime')
																			+ '\n暂无课程安排信息');
													$(weekId).attr('grade_id',
															'0');
													$(weekId).attr('class_num',
															'0');
												}
												str = str.substring(0,
														str.length - 1);
												str += "周数据未保存,因为已有其他老师为该班级排课";
												dreambox.alert(str);
											}

										}, 'json');
					});
});
// 切换课程时需要提示是否编辑
function changeEdit(_this) {
	// 检查是否需要提示修改
	var _active = $('.info-edit');
	for (i = 0; i < _active.length; i++) {
		var oldEdit = _active.eq(i);
		if (oldEdit.css('display') != 'none') {
			var temp1 = trim(oldEdit.parents('li').find('.info-show .l-class')
					.text());
			var temp2 = trim(oldEdit.find('.select .text').text())
					+ trim(oldEdit.parents('li').find('input').val()) + '班';
			if (temp1 != temp2) {
				var _str = "年级将由<span class='cblue'>(" + temp1
						+ ")</span>变更为<span class='cblue'>(" + temp2
						+ ")</span>";
				var _iconsEdit = oldEdit.find('.icons.ok');
				dreambox.confirm(_str, '确定要修改吗', '变更课程', function() {
					clickOk(_iconsEdit);
				});
				oldEdit.hide();
				oldEdit.siblings('.info-show').show();
			} else {
				oldEdit.hide();
				oldEdit.parents('li').find('.info-show').show();
			}
		}
	}
	_this.find('.info-show').hide();
	var _edit = _this.find('.info-edit');
	_edit.css('display', 'inline-block');
	// 重置css样式
	_this.parents('.course-ul').find('li.active').removeClass('active');
	_this.addClass('active');
	// 设置默认班级
	var _grade = _this.find('.l-class');
	var _gradeId = _grade.attr('grade_id');
	var _index = 3;
	if(_grade.text().indexOf('其他') != -1){
		_index = 2;
	}
	var _gradeName = _grade.text().substring(0, _index);
	var _classNum = _grade.attr('class_num');
	_this.find('.info-edit .select').attr('date-value', _gradeId);
	_this.find('.info-edit .text').text(_gradeName);
	_this.find('#classInput').val(_classNum);

}
function clickOk(_this) {
	var _infoShow = _this.parents('li').find('.info-show'), _infoEdit = _this
			.parent();
	_class = _infoEdit.find('.s-class input');
	if (_class.val().length == 0) {
		dreambox.alert('班级不能为空');
		return;
	}
	_infoShow.find('.l-course').text(_infoEdit.find('.s-course').text());
	var _grade = _infoEdit.find('.s-grade .text');
	var _info = _infoShow.find('.l-class');
	var oldCourseName = _infoShow.find('.l-course').attr('course_name');
	var oldGradeId = _info.attr('grade_id');
	var oldClassNum = _info.attr('class_num');
	_info.attr('grade_id', _grade.parents('.selectGroup').find('.select').attr(
			'date-value'));
	_info.attr('class_num', _class.val());
	_info.text(_grade.text() + _class.val() + '班');
	modifyCourse(_this.parents('li'), oldCourseName, oldGradeId, oldClassNum);
	_infoEdit.hide();
	_infoShow.show();
}

function delCourse(_this) {
	var $weeks = $('.time-box  .time-item:not(.disable)');

	var course_name = _this.siblings('.l-course').attr('course_name');
	var grade_id = _this.siblings('.l-class').attr('grade_id');
	var class_num = _this.siblings('.l-class').attr('class_num');
	for (i = 0; i < $weeks.length; i++) {
		$week = $weeks.eq(i);
		if ($week.attr('course_id') == course_name
				&& $week.attr('grade_id') == grade_id
				&& $week.attr('class_num') == class_num) {
			$week.removeClass('warning active hover');
			$week.find('em').remove();
		}
	}
}

function modifyCourse(_li, oldCourseName, oldGradeId, oldClassNum) {
	var $weeks = $('.time-box  .time-item:not(.disable)');
	var course_name = _li.find('.l-course').attr('course_name');
	var grade_id = _li.find('.l-class').attr('grade_id');
	var class_num = _li.find('.l-class').attr('class_num');
	for (i = 0; i < $weeks.length; i++) {
		$week = $weeks.eq(i);
		if ($week.attr('course_id') == oldCourseName
				&& $week.attr('grade_id') == oldGradeId
				&& $week.attr('class_num') == oldClassNum) {
			$week.attr('course_id', course_name);
			$week.attr('grade_id', grade_id);
			$week.attr('class_num', class_num);

			oldTitle = $week.attr('title');
			$week.attr('title', oldTitle.substring(0,
					oldTitle.lastIndexOf('：') + 1)
					+ _li.find('.l-class').text());

			$week.removeClass('warning');
			$week.addClass('active');
			$week.find('em').remove();
			$week.append('<em class="bottomrightdirection">√</em>');
		}
	}
}