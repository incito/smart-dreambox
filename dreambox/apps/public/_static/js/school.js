var option = {
	feed_doing : false,
	list_doing : false,
	teacher_page_size:8,
	teacher_page_num:1,
	teacher_page_count:0,
	course_page_size:8,
	course_page_num:1,
	course_page_count:0
};
//页面缓存对象
var cache = {
	feeds : new Map(),
	feedList : new Map(),
	teachers:new Array(),
	courses:new Array()
};
$(function() {
	//加载教师数据
	var sid = $('#sid').val();
	var param=['sid='+sid];
	$.get(U('public/Profile/getTeachers', param),null,function(res){
		if(res.status==1){
			cache.teachers=res.data;
			$('.teachers h6 .tc').text(res.data.length);
			option.teacher_page_count=Math.ceil(res.data.length/option.teacher_page_size);
			processTeachers();
			$('.teachers h6 a.prev').click(function(){
				if(--option.teacher_page_num>=1){
					processTeachers();
				}else{
					option.teacher_page_num=1;
				}
			});
			$('.teachers h6 a.next').click(function(){
				if(++option.teacher_page_num<=option.teacher_page_count){
					processTeachers();
				}else{
					option.teacher_page_num=option.teacher_page_count;
				}
			});
		}
	},'json');
	
	//加载课程数据
	var param=['sid='+sid,'term_id='+$('#term_id').val()];
	$.get(U('public/Profile/getCourses', param),null,function(res){
		if(res.status==1){
			cache.courses=res.data;
			$('.courses h6 .tc').text(res.data.length);
			option.course_page_count=Math.ceil(res.data.length/option.course_page_size);
			processCourses();
			$('.courses h6 a.prev').click(function(){
				if(--option.course_page_num>=1){
					processCourses();
				}else{
					option.course_page_num=1;
				}
			});
			$('.courses h6 a.next').click(function(){
				if(++option.course_page_num<=option.course_page_count){
					processCourses();
				}else{
					option.course_page_num=option.course_page_count;
				}
			});
		}
	},'json');
	
	// 签到记录翻页
	$('.feedback .toweek').click(
			function() {
				var mtime = $(this).attr('time');
				// 优先取缓存数据
				var feed = cache.feeds.get(mtime);
				if (feed != null) {
					processFeedback(feed);
				} else if (!option.feed_doing) {
					option.feed_doing = true;
					var param = [ "sid=" + sid, "mtime=" + mtime ];
					$.get(U('public/Profile/getWeekFeedback', param), null,
							function(res) {
								if (res.status == 1) {
									var data = res.data;
									cache.feeds.put(mtime, data);
									processFeedback(data);
								}
								option.feed_doing = false;
							}, 'json');
				}
			});
	// 显示详细签到列表
	$('.feedback .school-table table tr td:not(:first):not(:eq(7))').hover(
			function() {
				var _this = $(this);
				// 没有数据不处理
				if (trim(_this.text()) == 0) {
					return;
				}
				var status = _this.parent().attr('status');
				var week_day = _this.index() % 8;
				if (week_day == 7) {
					week_day = 0;
				}
				var week_num = $('#week_num').val();
				var term_id = $('#term_id').val();
				var courses = cache.feedList.get(term_id + "_" + week_num + "_"
						+ week_day + "_" + status);
				if (courses != null) {
					processFeedbackList(courses, _this);
				} else if (!option.list_doing) {
					var param = [ "term_id=" + term_id, "week_num=" + week_num,
							"week_day=" + week_day, 'status=' + status ];
					$.get(U('public/Profile/getFeedbackListAjax', param), null,
							function(res) {
								if (res.status == 1) {
									var data = res.data;
									cache.feedList.put(term_id + "_" + week_num
											+ "_" + week_day + "_" + status,
											data);
									processFeedbackList(data, _this);
								}
								option.list_doing = false;
							}, 'json')
				}
			}, function() {
				$('#clist').remove();
			});


})

// 处理并展示签到数据
function processFeedback(feed) {
	$feedDiv = $('.feedback');
	$('.active',$feedDiv).removeClass('active');
	$('a.prev', $feedDiv).attr('time', feed.preMondayTime);
	if (feed.preMondayTime == 0) {
		$('.pw', $feedDiv).hide();
	} else {
		$('.pw', $feedDiv).show();
	}

	$('a.next', $feedDiv).attr('time', feed.nextMondayTime);
	if (feed.nextMondayTime == 0) {
		$('.nw', $feedDiv).hide();
	} else {
		$('.nw', $feedDiv).show();
	}
	$('h1', $feedDiv).text(feed.term);

	$table = $('.school-table table', $feedDiv);
	for ( var i in feed.data) {
		var ret=feed.data[i];
		$('thead th:eq(' + i + ') span', $table).text(ret.date);
		$('tbody tr:eq(0) td:eq(' + i + ') div', $table).text(
				ret.notfeedback);
		$('tbody tr:eq(1) td:eq(' + i + ') div', $table).text(
				ret.feedbacked);
		if(ret.isToday){
			$('thead th:eq(' + i + ')').addClass('active');
			$('tbody tr:eq(0) td:eq(' + i + ')').addClass('active');
			$('tbody tr:eq(1) td:eq(' + i + ')').addClass('active');
		}
	}
	$('p.info span:first', $feedDiv).text(feed.teacherCount?feed.teacherCount:'0');
	$('p.info span:last', $feedDiv).text(feed.classCount?feed.classCount:'0');
	$('#week_num').val(feed.week_num);
	$('#term_id').val(feed.term_id);
}
function processFeedbackList(list, td) {
	$div = $("<div class='s-s-diolog' id='clist'><i class='b-left'></i><ul></ul></div>");
	$ul = $('ul', $div);
	var profileUrl = U('public/Profile/index') + "&uid=";
	for ( var i in list) {
		var v = list[i];
		$li = $("<li><a href='" + profileUrl + v.uid
				+ "' class='avatar tiny'><img src='" + v.avatar.avatar_small
				+ "' alt='头像' title='" + v.realname + "'></a><a href='"
				+ profileUrl + v.uid + "' class='name cblue'>" + v.realname
				+ "</a><label class='class-name'>" + v.course_name
				+ "</label><span class='line'>|</span><label class=''class'> "
				+ v.class_name
				+ "</label><span class='line'>|</span><label class='session'>"
				+ v.section_num + "</label><li>")
		$ul.append($li);
	}
	if ($('li', $ul).length > 0) {
		$("div", td).append($div);
	}
}
function processTeachers(){
	if(cache.teachers.length<1){
		return;
	}
	var pageNum=option.teacher_page_num;
	pageNum=Math.max(pageNum,0);
	pageNum=Math.min(pageNum,option.teacher_page_count);
	var pageArr=cache.teachers.slice((pageNum-1)*option.teacher_page_size,pageNum*option.teacher_page_size);
	if(pageArr.length>0){
		var profileUrl = U('public/Profile/index') + "&uid=";
		var t=$('.teachers dl');
		t.empty();
		for(var i in pageArr){
			var v=pageArr[i];
			t.append($("<dd><a class='avatar tiny' href='"+profileUrl+v.uid+"'><img title='"+v.realname+"' alt='头像' src='"+v.avatar.avatar_small+"'></a><p ><a  class='overtext' title='"+v.realname+"' href='"+profileUrl+v.uid+"'>"+v.realname+"</a></p></dd>"));
		}
	}
}
function processCourses(){
	if(cache.courses.length<1){
		return;
	}
	var pageNum=option.course_page_num;
	pageNum=Math.max(pageNum,0);
	pageNum=Math.min(pageNum,option.course_page_count);
	var pageArr=cache.courses.slice((pageNum-1)*option.course_page_size,pageNum*option.course_page_size);
	if(pageArr.length>0){
		var courseUrl = U('dreambox/CourseCenter/courseInfo') + "&id=";
		var c=$('.courses ul');
		c.empty();
		for(var i in pageArr){
			var v=pageArr[i];
			c.append("<li><a target='_blank' href='"+courseUrl+v.id+"' title='"+v.class_name+"'>"+v.class_name+"</a></li>");
		}
	}
}