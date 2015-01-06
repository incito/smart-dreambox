<?php
class CourseModel extends Model {
	public $tableName = 'db_course';
	public function _initialize() {
	}
	/**
	 * 获取当前学期的所有课程,该方法用于课表展示
	 */
	public function getSelectCourse($userId, $termId) {
		$count = M ( 'db_week' )->where ( 'term_id=' . $termId )->count ();
		return $this->db->query ( 'select * from ts_db_select_course where term_id=' . $termId . ' and user_id=' . $userId . ' and week_num <=' . $count . ' group by course_id,week_day,section_num' );
	}
	
	/**
	 * 获取一共有多少节
	 */
	public function getSections() {
		$section = M ( 'db_section' )->select ();
		return $section;
	}
	/**
	 * 获取课程列表
	 */
	public function getCourses() {
		return M ( 'db_course' )->where ( 'is_del=0 and is_close=0' )->select ();
	}
	
	/**
	 * 获取课程列表
	 */
	public function getGrade() {
		return M ( 'db_grade' )->select ();
	}
	
	/**
	 * 获取视频简介列表
	 */
	public function getVideoIntros() {
		$result = M ( 'db_video_intro' )->select ();
		return $result;
	}
	
	/**
	 * 获取当前学期有多少周
	 */
	public function getWeekByTerm($userId) {
		$term = model ( 'Term' )->getCurrentTerm ( $userId );
		return M ( 'db_week' )->where ( 'term_id=' . $term ['id'] )->order ( 'week_num asc' )->select ();
	}
	
	/**
	 * 获取课程类型,比如我是谁,我要去哪里
	 */
	public function getCourseType() {
		return $this->db->query ( 'select distinct (type.id), type.name FROM ts_db_course_type as type inner join ts_db_course as course on type.id = course.type ' );
	}
	/**
	 * 获取当前时间段的所选课程,以星期几第几节区分
	 */
	public function getCurrentTimeSelectCourse($userId, $condition) {
		$course = M ( 'db_select_course' )->where ( array (
				'user_id' => $userId,
				'week_num' => $condition ["week_num"],
				'week_day' => $condition ["week_day"],
				'section_num' => $condition ["section_num"],
				'term_id' => $condition ["term_id"] 
		) )->find ();
		// 查询该周起止时间
		$stime = $this->getTimeStr ( $condition ['stime'], $condition ["week_num"], 1 );
		$etime = $this->getTimeStr ( $condition ['stime'], $condition ["week_num"], 0 );
		// 如果当前老师第一周第一节已经有课程了
		if ($course) {
			// 查询课程名称
			$courseName = M ( 'db_course' )->where ( 'id=' . $course ['course_id'] )->getField ( 'class_name' );
			// 查询年级级
			$gradeName = M ( 'db_grade' )->where ( 'id=' . $course ['grade_id'] )->getField ( 'name' );
			
			$course ['course_name'] = $courseName;
			$course ['grade_name'] = $gradeName;
			$course ['stime'] = $stime;
			$course ['etime'] = $etime;
			$course ['status'] = 1;
			// 如果已签到 查询签到时间
			if ($course ['feedback_status'] == 1) {
				$signTime = M ( 'db_lesson_feedback' )->where ( 'course_id=' . $course ['id'] )->getField ( 'create_time' );
				$course ['sign_time'] = $signTime;
				$course ['status'] = 2;
			}
			
			return $course;
		}
		return array (
				'status' => 0,
				'stime' => $stime,
				'etime' => $etime 
		);
	}
	/**
	 * 新增或修改课表
	 */
	public function modifyCourse($userId, $data) {
		$term = model ( 'Term' )->getCurrentTerm ( $userId );
		$model = M ( 'db_select_course' );
		$time = time ();
		// 获取周数
		$weekData = explode ( ",", $data );
		
		$followModel = model ( 'CourseFollow' );
		$courseIds = array ();
		$errorWeeks = array ();
		foreach ( $weekData as $columns ) {
			$column = explode ( "-", $columns );
			// 先判断当前状态 0为已签到 1为未排课 2为已排课
			if ($column [0] == '0') {
				continue;
			}
			// 删除以前的数据
			$model->where ( array (
					'week_num' => $column [1],
					'week_day' => $column [2],
					'section_num' => $column [3],
					'user_id' => $userId,
					'term_id' => $term ['id'] 
			) )->delete ();
			
			if ($column [0] == '2') {
				$newRow = array (
						'week_num' => $column [1],
						'week_day' => $column [2],
						'section_num' => $column [3],
						'grade_id' => $column [4],
						'class_num' => $column [5],
						'course_id' => $column [6],
						'feedback_status' => 0,
						'user_id' => $userId,
						'term_id' => $term ['id'] 
				);
				$result = $this->selectRepeatCourse ( $userId, $term ['id'], $newRow );
				// 如果有其他老师占用该时间段
				if (count ( $result )) {
					array_push ( $errorWeeks, $column [1] );
					continue;
				}
				// 添加数据
				$result = $model->data ( $newRow )->add ();
				// 添加关注
				$follow = array (
						'uid' => $userId,
						'type' => 1,
						'ctime' => $time,
						'course_id' => $column [6] 
				);
				if (! in_array ( $follow [course_id], $courseIds )) {
					array_push ( $courseIds, $follow [course_id] );
					$followModel->addFollow ( $follow );
				}
			}
		}
		return $errorWeeks;
	}
	/**
	 * 获取可以编辑的课程
	 */
	public function getEditCourseByTime($userId, $condition) {
		$term = model ( 'Term' )->getCurrentTerm ( $userId );
		return M ( 'db_select_course' )->where ( array (
				'term_id' => $term [id],
				'user_id' => $userId,
				'week_day' => $condition ['week_day'],
				'section_num' => $condition ['section_num'] 
		) )->group ( 'course_id,grade_id,class_num' )->select ();
	}
	
	/**
	 * 查询是否可以在该时间段选择课程
	 */
	public function selectRepeatCourse($userId, $termId, $data) {
		$sql = "select id from ts_db_select_course where term_id =" . $termId . " and week_num=" . $data ['week_num'] . " and week_day=" . $data ['week_day'] . " and section_num=" . $data ['section_num'] . " and grade_id=" . $data ['grade_id'] . " and class_num=" . $data ['class_num'];
		if($userId != null && $userId > 0){
		    $sql = $sql." and user_id <>" . $userId;
		}
		return $this->db->query ( $sql );
	}
	
	/**
	 * 查询当前可以签到的课程
	 */
	public function queryNeedSignCourse($userId) {
		$term = M ( 'Term', 'dreambox' )->getCurrentTerm ( $userId );
		// 获取当前时间
		$time = strtotime ( date ( "Y-m-d" ) );
		$eweek = floor ( ($time - $term ['stime']) / (60 * 60 * 24 * 7) );
		// 获取周界定值
		for($i = $eweek; $i < $eweek + 5; $i ++) {
			$stime = $this->getTime ( $term ['stime'], $i, 1 );
			$etime = $this->getTime ( $term ['stime'], $i, 0 );
			if ($time >= $stime && $time <= $etime) {
				$week = $i;
				break;
			}
		}
		for($i = 0; $i < 7; $i ++) {
			$ctime = $this->getTime ( $term ['stime'], $week, $i );
			if ($ctime == $time) {
				$cday = $i;
				break;
			}
		}
		if ($i == 0) {
			$sql = "select * from (select *,(week_day+7)%8 as week_day_order from ts_db_select_course where user_id=" . $userId . " and term_id=" . $term ['id'] . " and feedback_status=0 " . "and week_num <=" . $week . ") a order by a.week_num,a.week_day_order,a.section_num";
		} else {
			$sql = "select * from (select *,(week_day+7)%8 as week_day_order from ts_db_select_course where user_id=" . $userId . " and term_id=" . $term ['id'] . " and feedback_status=0 " . "and (week_num <" . $week . " or (week_num=" . $week . " and week_day >0 and week_day <=" . $cday . "))) a order by a.week_num,a.week_day_order,a.section_num";
		}
		$data = M ()->query ( $sql );
		
		// 过滤过期的签到
		$config = M ( "db_system_config" )->where ( "key_name='expired_day'" )->find ();
		$expired_day = $config ['value'];
		if ($expired_day == 0) {
			return $data;
		}
		
		$index = 0;
		foreach ( $data as & $sc ) {
			$lessonTime = $this->getTimeStr ( $term ['stime'], $sc ['week_num'], $sc ['week_day'] );
			$diff = strtotime ( date ( 'Y-m-d' ) ) - strtotime ( $lessonTime );
			if ($diff > ($expired_day * 24 * 60 * 60)) {
				unset ( $data [$index] );
			}
			$index ++;
		}
		
		return $data;
	}
	/**
	 * 获取及时签到选课记录
	 */
	public function getCurrentDaySignCourse($userId) {
		$term = M ( 'Term', 'dreambox' )->getCurrentTerm ( $userId );
		// 获取当前时间
		$time = strtotime ( date ( "Y-m-d" ) );
		$eweek = floor ( ($time - $term ['stime']) / (60 * 60 * 24 * 7) );
		// 获取周界定值
		for($i = $eweek; $i < $eweek + 5; $i ++) {
			$stime = $this->getTime ( $term ['stime'], $i, 1 );
			$etime = $this->getTime ( $term ['stime'], $i, 0 );
			if ($time >= $stime && $time <= $etime) {
				$week = $i;
				break;
			}
		}
		for($i = 0; $i < 7; $i ++) {
			$ctime = $this->getTime ( $term ['stime'], $week, $i );
			if ($ctime == $time) {
				$cday = $i;
				break;
			}
		}
		$sql = "select * from (select *,(week_day+7)%8 as week_day_order from ts_db_select_course where user_id=" . $userId . " and term_id=" . $term ['id'] . " and feedback_status=0 and week_num=" . $week . " and week_day=" . $cday . ") a order by a.week_num,a.week_day_order";
		return M ()->query ( $sql );
	}
	/**
	 * 获取当前课程时间
	 */
	public function getTimeStr($stime, $week_num, $week_day) {
		$time = $this->getTime ( $stime, $week_num, $week_day );
		return date ( "Y-m-d", $time );
	}
	public function checkDate($week_num, $week_day, $term) {
		// 获取学期开始时间
		$time = $this->getTime ( $term ['stime'], $week_num, $week_day );
		if ($time > $term ["etime"] || $time < $term ["stime"]) {
			return date ( "Y-m-d", $time ) . "不在学期(" . date ( "Y-m-d", $term ["stime"] ) . '~' . date ( "Y-m-d", $term ["etime"] ) . ")范围内";
		}
		return null;
	}
	/**
	 * 获取当前课程时间
	 */
	public function getTime($stime, $week_num, $week_day) {
		$week_num = $week_day == 0 ? $week_num + 1 : $week_num;
		$w = date ( 'w', $stime );
		$temp1 = 60 * 60 * 24;
		$temp2 = 7 * $temp1;
		$time = $stime + ($week_num - 1) * $temp2 + ($week_day - $w) * $temp1;
		return $time;
	}
	/**
	 * 获取当前周的课程表
	 */
	public function getCurrentWeekCourse($condition) {
		return M ( 'db_select_course' )->where ( array (
				'term_id' => $condition ['term_id'],
				'user_id' => $condition ['user_id'],
				'week_num' => $condition ['week_num'] 
		) )->select ();
	}
	
	/**
	 * 根据id查询课程
	 *
	 * @param unknown $id        	
	 */
	public function getCourseById($id) {
		return M ( 'db_course' )->where ( 'id=' . $id )->find ();
	}
	
	/**
	 * 根据id查询年级
	 *
	 * @param unknown $id        	
	 */
	public function getGradeById($id) {
		return M ( 'db_grade' )->where ( 'id=' . $id )->find ();
	}
	
	/**
	 * 根据id查询第几节
	 *
	 * @param unknown $id        	
	 */
	public function getSectionById($id) {
		return M ( 'db_section' )->where ( 'id=' . $id )->find ();
	}
	
	/**
	 * 得到学期的开始时间
	 *
	 * @return string
	 */
	public function getCoursetime($stime, $week_num, $week_day) {
		$add_day = ($week_num - 1) * 7 + $week_day;
		
		$time = strtotime ( '+' . $add_day . ' day', $stime );
		
		return date ( "Y-m-d", $time );
	}
	
	/**
	 * 根据课程id查询所有章节
	 *
	 * @param unknown $course_id        	
	 */
	public function getHours($course_id) {
		return M ( 'db_class_hours' )->where ( 'hid=' . $course_id )->select ();
	}
	
	/**
	 * 签到页面修改选课
	 */
	public function updateCourse($data) {
		M ( 'db_select_course' )->where ( 'id=' . $data ['id'] )->save ( $data );
	}
	/**
	 * 签到页面删除选课
	 *
	 * @param unknown $id        	
	 * @param unknown $data        	
	 */
	public function removeCourse($id, $data) {
		M ( 'db_select_course' )->where ( 'id=' . $id )->delete ();
	}
	
	/**
	 * 获取课程介绍
	 */
	public function getCourseInfo() {
		$info = M ( 'db_course_info' )->find ();
		return $info ['value'];
	}
	public function getHasCourseWeeks($condition) {
		$weeks = M ( 'db_select_course' )->where ( $condition )->field ( 'week_num' )->group ( 'week_num' )->select ();
		return getSubByKey ( $weeks, 'week_num' );
	}
	
	/**
	 * 获取评论
	 */
	public function getComment($blogId) {
		$comment = M ( 'blog_comment' );
		$condition ['type'] = 3;
		$condition ['blog_id'] = $blogId;
		return $comment->where ( $condition )->select ();
	}
	/**
	 * 获得指定学校 指定学期 指定周的签到情况
	 */
	public function getSchoolFeedbacks($sid, $termId, $week = 1) {
		$data = S ( 'school_feedback_' . $sid . '_' . $termId . '_' . $week );
		if (! $data) {
			$map ['t.school_id'] = $sid;
			$map ['sc.term_id'] = $termId;
			$map ['sc.week_num'] = $week;
			// 签到记录
			$sql = 'SELECT  week_day,SUM(feedbacked) as feedbacked,SUM(notfeedback) as notfeedback FROM( select  sc.week_day,case feedback_status when 1 then 1 else 0 end as feedbacked,case feedback_status when 1 then 0 else 1 end as notfeedback from ts_db_select_course sc left join ts_db_term t on sc.term_id=t.id where t.school_id=' . $sid . ' and sc.term_id= ' . $termId . ' and sc.week_num=' . $week . ' ) as t GROUP BY week_day';
			$data ['data'] = $this->query ( $sql );
			// 教师数
			$sql = 'select count(1) as count from(select DISTINCT sc.user_id from ts_db_select_course sc where sc.term_id=' . $termId . ') as t';
			$data ['teacherCount'] = $this->query ( $sql );
			$data ['teacherCount'] = $data ['teacherCount'] ['0'] ['count'];
			// 班级数
			$sql = 'select count(1) as count from(select distinct sc.grade_id,sc.class_num from ts_db_select_course sc where sc.term_id=' . $termId . ') as t';
			$data ['classCount'] = $this->query ( $sql );
			$data ['classCount'] = $data ['classCount'] ['0'] ['count'];
			// 缓存1分钟
			if ($data ['data']) {
				S ( 'school_feedback_' . $sid . '_' . $termId . '_' . $week, $data, 60 );
			}
		}
		return $data;
	}
	/**
	 * 判断当前学期是否有课程
	 */
	public function hasCourseByTerm($uid, $termId) {
		$map ['user_id'] = $uid;
		$map ['term_id'] = $termId;
		return M ( 'db_select_course' )->where ( $map )->count ();
	}
	public function getSchoolCourses($sid, $term_id) {
		$data = S ( 'school_courses' . $sid . '_' . $term_id );
		if ($data) {
			return $data;
		}
		$sql = 'select id,class_name from(select id,class_name,count(user_id) as count from (select distinct c.id,c.class_name,sc.user_id from ts_db_course c left join ts_db_select_course sc on c.id=sc.course_id where sc.term_id=' . $term_id . ')as temp group by id ) as tt order by count desc';
		$data = $this->query ( $sql );
		// 缓存1分钟
		if ($data) {
			S ( 'school_courses' . $sid . '_' . $term_id, $data, 60 );
		}
		return $data;
	}
}