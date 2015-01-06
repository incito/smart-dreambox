$(function() {
	// 初始化
	$('.sider-nav-ul li span.active').stop().animate({
		'left' : 2
	}, 400, function() {
	});
	// 点击周
	$('.sider-nav-ul li span')
			.bind(
					{
						click : function(event) {
							var _this = $(this);
							$('.sider-nav-ul li span.active').stop(false, true)
									.animate({
										'left' : 40
									}, 400, function() {
										$(this).removeClass('active');
									});
							_this
									.stop(false, true)
									.removeClass('hover')
									.addClass('active')
									.animate(
											{
												'left' : 2
											},
											400,
											function() {
												week_num = _this.parent().attr(
														'week_num');
												html = '<tr class="odd"><td class="ftth">第一节</td><td></td><td></td><td></td><td></td><td></td><td></td><td class="nobdr"></td></tr><tr><td class="ftth">第二节</td><td></td><td></td><td></td><td></td><td></td><td></td><td class="nobdr"></td></tr><tr class="odd"><td class="ftth">第三节</td><td></td><td></td><td></td><td></td><td></td><td></td><td class="nobdr"></td></tr><tr><td class="ftth">第四节</td><td></td><td></td><td></td><td></td><td></td><td></td><td class="nobdr"></td></tr><tr class="odd"><td class="ftth">第五节</td><td></td><td></td><td></td><td></td><td></td><td></td><td class="nobdr"></td></tr><tr><td class="ftth">第六节</td><td></td><td></td><td></td><td></td><td></td><td></td><td class="nobdr"></td></tr><tr class="odd"><td class="ftth">第七节</td><td></td><td></td><td></td><td></td><td></td><td></td><td class="nobdr"></td></tr><tr><td class="ftth nobdb">第八节</td><td class="nobdb"></td><td class="nobdb"></td><td class="nobdb"></td><td class="nobdb"></td><td class="nobdb"></td><td class="nobdb"></td><td class="nobdr nobdb"></td></tr>';
												$('tbody').html(html);
												var data = {
													'week_num' : week_num,
													'uid' : $('#otherId').val()
												}
												$
														.post(
																U('dreambox/Course/previewCourse'),
																data,
																function(json) {
																	for (i in json.data) {
																		course = json.data[i];
																		var week_day = course.week_day;
																		if (week_day == 0) {
																			week_day = 7;
																		}
																		$td = $(
																				'tr')
																				.eq(
																						course.section_num)
																				.find(
																						'td')
																				.eq(
																						week_day);
																		_divStr='<div class="sch-wrap"><a style="margin-top: 7px;" href="javascript:;"> <span>'+course.class_name+'</span> <span class="sch-wrap-detail">'+course.grade_name+course.class_num+'班</span> </a></div>';

																		_div = $(_divStr);
																		$td
																				.append(_div);
																	}
																	_ths = $('th span');
																	cdate = eval("("+json.info+")");
																	for (j = 0; j < _ths.length; j++) {
																		_ths
																				.eq(
																						j)
																				.text(
																						cdate[j])
																	}
																}, 'json');
											});
						},
						mouseover : function(event) {
							var _this = $(this);
							if (_this.hasClass('active')) {
								return;
							}
							_this.stop(true, true).addClass('hover').animate({
								'left' : 2
							}, 400, function() {
							});
						},
						mouseleave : function(event) {
							var _this = $(this);
							if (_this.hasClass('active')) {
								return;
							}
							_this.stop(false, true).animate({
								'left' : 40
							}, 400, function() {
								_this.removeClass('hover');
							});
						}

					});
	$('.ss-sider > span.prev').click(function(event) {
		/* Act on the event */

	});
	// sider-nav 滚动
	var size = $('.sider-nav-ul li span').size(), hg = $(
			'.sider-nav-ul li span:first').innerHeight();
	$('.sider-nav-ul').height(Math.ceil(size / 14) * 14 * hg);
	$('.ss-sider > span.prev')
			.click(
					function(event) {
						var itemsTop = parseInt($('.sider-nav-ul').css('top')), step = $(
								'.sider-nav-ul li span:first').innerHeight() * 14;
						// 防再点击
						if (itemsTop % step != 0) {
							return;
						}
						var top = itemsTop + step;
						if (top <= 0) {
							$('.sider-nav-ul').animate({
								'top' : top
							}, 400, 'linear');
						}
					});
	$('.ss-sider > span.next')
			.click(
					function(event) {
						var itemsTop = parseInt($('.sider-nav-ul').css('top')), step = $(
								'.sider-nav-ul li span:first').innerHeight() * 14;
						// 防再点击
						if (itemsTop % step != 0) {
							return;
						}
						var top = itemsTop - step;
						if (top >= -($('.sider-nav-ul').innerHeight() - step)) {
							$('.sider-nav-ul').animate({
								'top' : top
							}, 400, 'linear');
						}
					});
	locatWeek();
});

function locatWeek(){
	var week_num = parseInt($('.sider-nav-ul li span.active').parent().attr('week_num'));
	var count = Math.floor(week_num/14);
	for(i=0;i<count;i++){
		$('.ss-sider > span.next').click();
	}
}