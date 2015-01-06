$(function() {
	initCss();
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
						if ($('.dialog-box p.active').length == 0) {
							dreambox.alert('请先选择一门课程，再进行排课');
							return;
						}
						if ($('.select label.text').text().length == 0) {
							dreambox.alert('请先选择年级，再进行排课');
							return;
						}
						if ($('#classInput').val().length == 0) {
							dreambox.alert('请先确定班级，再进行排课');
							return;
						}
						if (_this.hasClass('warning')) {
							eleClick = _this;
							name1 = trim(_this.attr('courseName'));
							gradeName1 = trim(_this.attr('gradeName'));
							name2 = trim($('.dialog-box p.active').attr('name'));
							gradeName2 = trim($('.select label.text').text())
									+ trim($('#classInput').val() + "班");
							if ((name1 + gradeName1) == (name2 + gradeName2)) {
								// 取消排课
								_this.removeClass('warning');
								_this.addClass('rm');
								_this.find('em').remove();
								_this.attr('title', '起止时间：'
										+ _this.attr('setime') + '\n暂无课程安排信息');
								return;
							}
							_info = "第" + _this.attr('id').substring(5) + "周("
									+ _this.attr('setime')
									+ ")课程将由<span class='cblue'>"
									+ _this.attr('courseName') + "("
									+ _this.attr('gradeName')
									+ ")</span>变更为<span class='cblue'>"
									+ $('.dialog-box p.active').attr('name')
									+ "(" + $('.select label.text').text()
									+ $('#classInput').val() + "班)</span>";
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
												var arr = _this.attr('title')
														.split('\n');
												_title = arr[0]
														+ '\n课程名称：'
														+ $(
																'.dialog-box p.active')
																.attr('name')
														+ '\n班级名称：'
														+ $(
																'.select label.text')
																.text()
														+ $('#classInput')
																.val() + "班";
												_this.attr('title', _title);
											});
						} else if (_this.hasClass('active')) {
							// 取消排课
							_this.removeClass('active');
							_this.find('em').remove();
							_this.attr('title', '起止时间：' + _this.attr('setime')
									+ '\n暂无课程安排信息');
						} else {

							course.checkDateAndCanSelect(_this);
						}
					});

	// choice
	$('.choice .ele-table').click(
			function(event) {
				var _this = $(event.target);
				if (_this.is('p')) {
					$('.choice p.active').removeClass('active');
					_this.addClass('active');
					$(this).stop().slideUp(
							400,
							function() {
								$('.choice-input').val(_this.attr('name'))
										.show();
								$('.choice-input').attr('course_id',
										_this.attr('course_id'));
							});
					$(this).parents('.choice').find('.title i').toggleClass(
							'arrow_up').toggleClass('arrow_down');
				}
			});
	$('.choice-input').click(
			function(event) {
				var _parent = $(this).parents('.choice');
				$(this).hide();
				_parent.find('.ele-table').stop().slideDown(400);
				_parent.find('.title i').removeClass('arrow_down').addClass(
						'arrow_up');
			});
	$('.choice .title i').click(function(event) {
		var _this = $(this), _parent = _this.parents('.choice');
		if (_this.is('.arrow_up')) {
			_parent.find('.ele-table').stop().slideUp(400, function() {
				_parent.find('.choice-input').show();
			});
			_this.removeClass('arrow_up').addClass('arrow_down');
		} else {
			_parent.find('.choice-input').hide();
			_parent.find('.ele-table').stop().slideDown(400);
			_this.removeClass('arrow_down').addClass('arrow_up');
		}
	});

	$('#classInput').keyup(function() {
		var $input = $('#classInput');
		if ($input.val().length > 3) {
			dreambox.alert('班级不能超过3位数');
		}
		$input.val($input.val().replace(/[^\d]+/, '').substring(0, 3));

	});
	$('#classInput').mouseout(function() {
		var $input = $('#classInput');
		$input.val($input.val().replace(/[^\d]+/, '').substring(0, 3));
	});

	$('.select-items li').click(function() {
		if ($('#classInput').val().length != 0) {
			var data = {
				week_day : $('#week_day').val(),
				section_num : $('#section_num').val(),
				grade_id : $(this).attr('date-value'),
				class_num : $('#classInput').val()
			};
			getCourseInfo(data);
		}
	});
	// 全选
	$('.course-check')
			.click(
					function(event) {
						/* Act on the event */
						if ($('.dialog-box p.active').length == 0) {
							dreambox.alert('请先选择一门课程，再进行排课');
							return;
						}
						if ($('.select label.text').text().length == 0) {
							dreambox.alert('请先选择年级，再进行排课');
							return;
						}
						if ($('#classInput').val().length == 0) {
							dreambox.alert('请先确定班级，再进行排课');
							return;
						}
						// 全选
						_this = $(this);
						if (_this.find('i.icons').hasClass('c-checked')) {
							var _items = $('.time-box  .time-item:not(.active,.disable,.warning)');
							var weeks = '';
							for ( var i = 0; i < _items.length; i++) {
								var _item = _items.eq(i);
								var _week = _item.attr('id').substring(5);
								weeks += _week;
								weeks += ',';
							}
							weeks = weeks.substring(0, weeks.length - 1);
							var data = {
								week_nums : weeks,
								week_day : $('#week_day').val(),
								section_num : $('#section_num').val(),
								grade_id : $('.course-part .select').attr(
										'date-value'),
								class_num : $('#classInput').val(),
								uid : $('#teacherId').val()
							};
							$
									.post(
											U('dreambox/Course/checkAllCourse'),
											data,
											function(json) {
												for ( var i = 0; i < _items.length; i++) {
													var _item = _items.eq(i);
													var itemId = _item.attr(
															'id').substring(5)
													if (containsWeek(json.data,
															itemId)) {
														continue;
													}
													_item.addClass('active');
													_item
															.append('<em class="bottomrightdirection">√</em>');
													_item
															.attr(
																	'title',
																	'起止时间：'
																			+ _item
																					.attr('setime')
																			+ '\n课程名称：'
																			+ trim($(
																					'.choice-input')
																					.val())
																			+ '\n班级名称：'
																			+ trim($(
																					'.course-part .select')
																					.text())
																			+ $(
																					'#classInput')
																					.val()
																			+ '班');
												}
												_this.find('span').text('取消已选中课程');
											}, 'json');
						} else {
							// 去全选
							_this.find('span').text('选中全部空余周');
							$('.time-box  .time-item.active').click();
						}
						$(this).find('i.icons').toggleClass('c-checked')
								.toggleClass('c-check');

					});
	$('input.footer-cancel').click(function() {
		$('#inner_box').empty();
	});

	$('input.footer-submit').click(function() {
		if (!course.checkSubmit()) {
			dreambox.alert("请填写课程和班级信息.");
			return;
		}
		
		var active = $('.time-box  .time-item.active');
		if(active.length==0){
			dreambox.alert("请先选择排课周次.");
			return;
		}
		var weekDay = $('#week_day').val() + '-';
		var sectionNum = $('#section_num').val() + '-';
		var gradeId = $('.course-part .select').attr('date-value') + '-';
		var classNum = $('#classInput').val() + '-';
		var courseId = $('.choice-input').attr('course_id');
		var str = weekDay + sectionNum + gradeId + classNum+courseId;
		var data = '';
		for (i = 0; i < active.length; i++) {
			var _this = active.eq(i);
			data += ',';
			data += '2-';
			data += _this.attr('id').substring(5) + '-';
			data += str;
		}
		var disActive = $('.time-box  .time-item:not(.active,.disable,.warning)');
		for (i = 0; i < disActive.length; i++) {
			var _this = disActive.eq(i);
			data += ',';
			data += '1-';
			data += _this.attr('id').substring(5) + '-';
			data += str;
		}
		var uid = $('#teacherId').val();
		$.post(U('dreambox/Course/modifyCourse'), {
			param : data.substring(1),
			uid : $('#teacherId').val()
		}, function(res) {
			if (res.status == 1) {
				$('#inner_box').empty();
				window.location.href = U('dreambox/Course/showCourse')+"&uid="+ uid;
			}

		}, 'json');
	});
})
var course = {};
function getCourseInfo(data) {
	$('.time-box .time-item').removeClass('active disable warning');
	$.post(U('dreambox/Course/queryWeekStatus'), data, function(res) {
		res = eval(res);
		if (res.status == 1) {
			for ( var i in res.data) {
				$week = $('#week_' + i);
				d = res.data[i];
				$setime = "起止时间：" + d.stime + "~" + d.etime + "\n";
				$courseName = "课程名称：" + d.course_name + "\n";
				$gradeName = "班级名称：" + d.grade_name + d.class_num + "班\n";
				$week.removeClass('active disable warning');
				$week.attr("setime", d.stime + "~" + d.etime);
				if (d.status == 0) {
					$week.attr("title", $setime + '暂无课程安排信息');
				} else if (d.status == 1) {
					$week.addClass('warning');
					$week.attr("courseName", d.course_name);
					$week.attr("gradeName", d.grade_name + d.class_num + "班");
					$week.attr("title", $setime + $courseName + $gradeName);
					$week.attr("oldCourseId", d.course_id);
					$week.attr("oldGradeId", d.grade_id);
					$week.attr("oldClassNum", d.class_num);
					$week.append("<em class='toprightdirection'></em>");
				} else if (d.status == 2) {
					$week.addClass('disable');
					$signTime = "签到时间：" + d.sign_time + "\n";
					$week.attr("title", $setime + $courseName + $gradeName
							+ $signTime);
					$week.append("<em class='toprightdirection'></em>");
				}
			}
		} else {
			ui.box.error('获取信息失败');
		}
	}, 'json')
}

function getInitStatus() {
	var data = {
		week_day : $('#week_day').val(),
		section_num : $('#section_num').val(),
		uid : $('#teacherId').val()
	};
	getCourseInfo(data);

}

function initCss() {
	_ths = $('.dialog-box th');
	for (i = 0; i < _ths.length; i++) {
		_th = _ths.eq(i);
		if (i == 0) {
			_th.addClass('ftth');
		} else if (i % 2 == 1) {
			_th.addClass('even');
		}
		if (i == (_ths.length - 1)) {
			_th.addClass('nbdr');
		}
	}
	_trs = $('.dialog-box tr');
	for (i = 0; i < _trs.length; i++) {
		_tds = _trs.eq(i).find('td');
		for (j = 0; j < _tds.length; j++) {
			_td = _tds.eq(j);
			if (j == 0) {
				_td.addClass('tc');
			} else if (j % 2 == 1) {
				_td.addClass('even');
			}
			if (j == (_tds.length - 1)) {
				_td.addClass('nbdr');
			}
		}
	}
}
function containsWeek(data, id) {
	for ( var i in data) {
		var _week = data[i];
		if (_week == id) {
			return true;
		}
	}
	return false;
}
course.checkDateAndCanSelect = function(_this) {
	// 检查日期
	// 排课
	var str = _this.attr('id').substring(5) + "-" + $('#week_day').val() + "-"
			+ $('#section_num').val() + "-"
			+ $('.course-part .select').attr('date-value') + "-"
			+ $('#classInput').val();
	var data = {
		week_num : _this.attr('id').substring(5),
		week_day : $('#week_day').val(),
		uid : $('#teacherId').val(),
		data : str
	};
	$.post(U('dreambox/Course/checkDateAndCanSelect'), data, function(res) {
		res = eval(res);
		// 如果日期合法
		if (res.status == 1) {
			_this.addClass('active');
			_this.append('<em class="bottomrightdirection">√</em>');
			_this.attr('title', '起止时间：' + _this.attr('setime') + '\n课程名称：'
					+ trim($('.choice-input').val()) + '\n班级名称：'
					+ trim($('.course-part .select').text())
					+ $('#classInput').val() + '班');
		} else {
			dreambox.alert(res.info);
		}
	}, 'json');
}
course.checkNum = function(obj) {
	obj.val(obj.val().replace(/[^\d]+/, ''));
}
course.checkSubmit = function() {
	var gradeW = $('.course-part .select');
	if (gradeW.length == 0) {
		return false;
	}
	var grade = gradeW.attr('date-value');
	if (grade.length = 0 || grade == '0') {
		return false;
	}
	var course_idW = $('.choice-input');
	if (course_idW.length == 0) {
		return false;
	}
	var course_id = course_idW.attr('course_id');
	if (course_id == undefined || course_id.length == 0 || course_id == '0') {
		return false;
	}
	var cl = $('#classInput').val()
	return cl.length > 0 && cl != 0;
}
