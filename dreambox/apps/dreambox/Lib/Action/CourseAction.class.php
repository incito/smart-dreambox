<?php
class CourseAction extends Action {
	private $courseModel;
	/**
	 * 初始化，配置内容标题
	 *
	 * @return void
	 */
	public function _initialize() {
		$this->courseModel = M ( 'Course' );
	}
	
	/**
	 * 显示学期课表信息
	 */
	public function showCourse() {
		if (! CheckPermission ( 'dreambox_normal', 'select_class' )) {
			$this->error ( '对不起，您没有权限进行该操作！' );
		}
		$uid = $_REQUEST ['uid'] ? $_REQUEST ['uid'] : $this->mid;
		$term = model ( 'Term' )->getCurrentTerm ( $uid );
		if (! $term) {
			$this->error ( '你还未创建学期' );
		}
		$selectClass = $this->courseModel->getSelectCourse ( $uid, $term ['id'] );
		$sections = $this->courseModel->getSections ();
		$courses = $this->courseModel->getCourses ();
		$grades = $this->courseModel->getGrade ();
		// 检查是否有权限为他人排课,如果传入uid参数 说明是管理员在给其他人排课
		if (CheckPermission ( 'dreambox_normal', 'modify_school_course' )) {
			// 获取该学校名单
			$teachers = M ()->query ( 'SELECT uid, realname FROM ts_user_verified WHERE sid = ( SELECT sid FROM ts_user_verified WHERE uid = ' . $this->mid . ' ) AND type = 0 AND verified = \'1\'' );
			// 判断当前人员是否为该学校教师
			$flag = false;
			foreach ( $teachers as $teacher ) {
				if ($teacher ['uid'] == $uid) {
					$flag = true;
					break;
				}
			}
			if(!$flag){
				$this->error ( '该老师不是你学校的教师，你不能给他排课' );
			}
			$this->assign ( 'teachers', $teachers );
			$this->assign ( 'isAdmin', 'true' );
		} else if ($_REQUEST ['uid'] && $_REQUEST ['uid'] != $this->mid) { // 如果不是管理员 并且传入的uid不是自己的uid
			$this->error ( '你不是管理员，不能为其他老师排课' );
		}
		$realName = M ( 'user_verified' )->where ( 'uid=' . $uid )->getField ( 'realname' );
		
		$this->assign ( 'uid', $uid );
		$this->assign ( 'termName', mb_substr ( $term ['name'], 0, 7, 'utf8' ) );
		$this->assign ( 'sections', $sections );
		$this->assign ( 'sections', $sections );
		$this->assign ( 'selectClass', $selectClass );
		$this->assign ( 'courses', $courses );
		$this->assign ( 'grades', $grades );
		$this->assign ( 'realname', $realName );
		$this->assign ( 'classSize', count ( $selectClass ) );
		$this->assign ( 'tab', 'showCourse' );
		$this->display ( 'index' );
	}
	/**
	 * 是否需要显示学期信息
	 */
	public function isShowTerm() {
		if (! CheckPermission ( 'dreambox_normal', 'select_class' )) {
			$this->error ( '对不起，您没有权限进行该操作！' );
		}
		$termModel = model ( 'Term' );
		$term = $termModel->getCurrentTerm ( $this->mid );
		if (empty ( $term )) { // 没有学期
			$term ['status'] = '0';
			$term ['stime'] = $termModel->getDefaultStime ();
			$term ['etime'] = $termModel->getDefaultEtime ();
		} else if ($term ['status'] == 0) { // 管理员还未确认
			$term ['stime'] = date ( 'Y-m-d', $term ['stime'] );
			$term ['etime'] = date ( 'Y-m-d', $term ['etime'] );
		} else {
			// 管理员已经确认
			echo 'showCourse';
			return;
		}
		$this->assign ( 'stime', $term ['stime'] );
		$this->assign ( 'etime', $term ['etime'] );
		$this->assign ( 'term', $term );
		
		// 判断当前操作人员是否为管理员
		if (CheckPermission ( 'dreambox_normal', 'modify_term' )) {
			// 查询学校信息
			$user = M ( 'user_verified' )->where ( 'uid=' . $this->mid )->find ();
			$school = M ( 'db_school' )->where ( 'id=' . $user ['sid'] )->find ();
			$schoolMaster = "";
			if (! empty ( $school ['remark'] )) {
				$remark = explode ( '|', $school ['remark'] );
				$schoolMaster = $remark [0];
			}
			$users = M ( 'user_verified' )->where ( 'sid=' . $user ['sid'] . ' AND verified=\'1\' AND type=0' )->select ();
			$this->assign ( 'schoolMaster', $schoolMaster );
			$this->assign ( 'school', $school );
			$this->assign ( 'user', $user );
			$this->assign ( 'users', $users );
			$this->display ( 'termInfo' );
		} else {
			$this->display ( 'term' );
		}
	}
	
	/**
	 * 去选课
	 */
	public function selectCourse() {
		if (! CheckPermission ( 'dreambox_normal', 'select_class' )) {
			$this->error ( '对不起，您没有权限进行该操作！' );
		}
		// 获取星期几
		$weekDay = $_GET ['week_day'];
		// 获取第几节
		$sectionNum = $_GET ['section_num'];
		// 获取老师uid
		$uid = $_REQUEST ['uid'] ? $_REQUEST ['uid'] : $this->mid;
		// 获取课程类型
		$courseType = $this->courseModel->getCourseType ();
		// 获取当前所有课程
		$courses = $this->courseModel->getCourses ();
		// 获取当前学期有多少周
		$weeks = $this->courseModel->getWeekByTerm ( $uid );
		// 获取年级
		$grades = $this->courseModel->getGrade ();
		
		$this->assign ( 'teacherId', $uid );
		$this->assign ( 'courseType', $courseType );
		$this->assign ( 'courses', $courses );
		$this->assign ( 'week_day', $weekDay );
		$this->assign ( 'section_num', $sectionNum );
		$this->assign ( 'weeks', $weeks );
		$this->assign ( 'grades', $grades );
		$this->assign ( 'weekLength', count ( $weeks ) );
		$this->assign ( 'lastWeek', floor ( count ( $weeks ) / 8 ) * 8 + 1 );
		$this->display ( 'selectCourse' );
	}
	/**
	 * 编辑课程
	 */
	public function editCourse() {
		if (! CheckPermission ( 'dreambox_normal', 'select_class' )) {
			$this->error ( '对不起，您没有权限进行该操作！' );
		}
		// 获取星期几
		$weekDay = $_GET ['week_day'];
		// 获取第几节
		$sectionNum = $_GET ['section_num'];
		// 获取老师uid
		$uid = $_REQUEST ['uid'] ? $_REQUEST ['uid'] : $this->mid;
		$condition = array (
				'week_day' => $weekDay,
				'section_num' => $sectionNum 
		);
		// 获取当前时间段的所有课程
		$editCourse = $this->courseModel->getEditCourseByTime ( $uid, $condition );
		// 获取当前所有课程
		$courses = $this->courseModel->getCourses ();
		// 获取当前学期有多少周
		$weeks = $this->courseModel->getWeekByTerm ( $uid );
		// 获取年级
		$grades = $this->courseModel->getGrade ();
		
		$this->assign ( 'teacherId', $uid );
		$this->assign ( 'week_day', $weekDay );
		$this->assign ( 'section_num', $sectionNum );
		$this->assign ( 'editCourse', $editCourse );
		$this->assign ( 'courses', $courses );
		$this->assign ( 'weeks', $weeks );
		$this->assign ( 'grades', $grades );
		$this->assign ( 'weekLength', count ( $weeks ) );
		$this->assign ( 'lastWeek', floor ( count ( $weeks ) / 8 ) * 8 + 1 );
		$this->display ( 'editCourse' );
	}
	/**
	 * 根据年级 班级 星期几 第几节 查询课程状态
	 */
	public function queryWeekStatus() {
		$uid = $_POST ['uid'] ? $_POST ['uid'] : $this->mid;
		$term = model ( 'Term' )->getCurrentTerm ( $uid );
		$condition = array (
				'week_day' => $_POST ['week_day'],
				'section_num' => $_POST ['section_num'],
				'term_id' => $term ['id'],
				'stime' => $term ['stime'] 
		);
		
		// 获取当前学期有多少周
		$weeks = $this->courseModel->getWeekByTerm ( $uid );
		$data = array ();
		foreach ( $weeks as $week ) {
			$key = $week ['week_num'];
			$condition ['week_num'] = $key;
			$data [$key] = $this->courseModel->getCurrentTimeSelectCourse ( $uid, $condition );
		}
		
		$this->ajaxReturn ( $data );
	}
	/**
	 * 修改课程
	 */
	public function modifyCourse() {
		if (! CheckPermission ( 'dreambox_normal', 'select_class' )) {
			$this->error ( '对不起，您没有权限进行该操作！' );
		}
		$uid = $_POST ['uid'] ? $_POST ['uid'] : $this->mid;
		$errorWeeks = $this->courseModel->modifyCourse ( $uid, $_POST ['param'] );
		if (count ( $errorWeeks ) > 0) {
			$this->ajaxReturn ( $errorWeeks, '', 0 );
		} else {
			$this->ajaxReturn ( $errorWeeks, '', 1 );
		}
	}
	/**
	 * 全选课程
	 */
	public function checkAllCourse() {
		$week_nums = $_POST ['week_nums'];
		$week_day = $_POST ['week_day'];
		$section_num = $_POST ['section_num'];
		$grade_id = $_POST ['grade_id'];
		$class_num = $_POST ['class_num'];
		$weeks = explode ( ',', $week_nums );
		$uid = $_POST ['uid'] ? $_POST ['uid'] : $this->mid;
		$errorWeeks = array ();
		// 获取学期
		$term = model ( 'Term' )->getCurrentTerm ( $uid );
		foreach ( $weeks as $week ) {
			$condition = array (
					'week_num' => $week,
					'week_day' => $week_day,
					'section_num' => $section_num,
					'grade_id' => $grade_id,
					'class_num' => $class_num 
			);
			// 检查时间
			$result = $this->courseModel->checkDate ( $week, $week_day, $term );
			if ($result) {
				array_push ( $errorWeeks, $week );
				continue;
			}
			$result = $this->courseModel->selectRepeatCourse ( $uid, $term ['id'], $condition );
			// 判断是否有重复数据
			if (count ( $result ) != 0) {
				array_push ( $errorWeeks, $week );
			}
		}
		$this->ajaxReturn ( $errorWeeks );
	}
	/**
	 * 检查日期是否合法和检查是否可选中
	 */
	public function checkDateAndCanSelect() {
		$week_num = $_POST ['week_num'];
		$week_day = $_POST ['week_day'];
		$uid = $_POST ['uid'] ? $_POST ['uid'] : $this->mid;
		// 获取学期
		$term = model ( 'Term' )->getCurrentTerm ( $uid );
		$result = $this->courseModel->checkDate ( $week_num, $week_day, $term );
		// 先检查时间
		if ($result) {
			$this->ajaxReturn ( null, $result, 0 );
		} else {
			// 检查课程是否已被选中
			$data = $_POST ['data'];
			$arr = explode ( ',', $data );
			foreach ( $arr as $row ) {
				$temp = explode ( '-', $row );
				$condition = array (
						'week_num' => $temp [0],
						'week_day' => $temp [1],
						'section_num' => $temp [2],
						'grade_id' => $temp [3],
						'class_num' => $temp [4] 
				);
				$result = $this->courseModel->selectRepeatCourse ( $uid, $term ['id'], $condition );
				// 判断是否有重复数据
				if (count ( $result ) != 0) {
					$this->ajaxReturn ( null, "当前时间段已经有梦想老师为该班级排课", 0 );
					return;
				}
			}
		}
		$this->ajaxReturn ( null, '', 1 );
	}
	/**
	 * 预览周课表
	 */
	public function previewCourse() {
		$id = empty ( $_REQUEST ['uid'] ) ? $this->mid : $_REQUEST ['uid'];
		$week_num = empty ( $_REQUEST ['week_num'] ) ? null : $_REQUEST ['week_num'];
		$termModel = model ( 'Term' );
		$term = $termModel->getCurrentTerm ( $id );
		if (empty ( $term )) {
			$this->error ( "还未创建新学期或学期已经过期" );
		}
		// 获取所有周
		$weeks = M ( 'db_week' )->where ( 'term_id=' . $term ['id'] )->select ();
		
		$time = strtotime ( date ( "Y-m-d" ) );
		// 如果当前时间不在学期范围内,默认为第一周
		if (empty ( $week_num ) && $time <= $term ['stime']) {
			$week_num = 1;
		}
		$stime = null;
		$etime = null;
		// 如果当前周为空
		if (empty ( $week_num )) {
			// 根据当前时间获取周
			$eweek = floor ( ($time - $term ['stime']) / (60 * 60 * 24 * 7) );
			for($i = $eweek; $i < $eweek + 5; $i ++) {
				$stime = $this->courseModel->getTime ( $term ['stime'], $i, 1 );
				$etime = $this->courseModel->getTime ( $term ['stime'], $i, 0 );
				if ($time >= $stime && $time <= $etime) {
					$week_num = $i;
					break;
				}
			}
		} else {
			$stime = $this->courseModel->getTime ( $term ['stime'], $week_num, 1 );
			$etime = $this->courseModel->getTime ( $term ['stime'], $week_num, 0 );
		}
		$day = 60 * 60 * 24;
		$stime1 = date ( "Y-m-d", $stime );
		$stime2 = date ( "Y-m-d", $stime + 1 * $day );
		$stime3 = date ( "Y-m-d", $stime + 2 * $day );
		$stime4 = date ( "Y-m-d", $stime + 3 * $day );
		$stime5 = date ( "Y-m-d", $stime + 4 * $day );
		$stime6 = date ( "Y-m-d", $stime + 5 * $day );
		$stime7 = date ( "Y-m-d", $stime + 6 * $day );
		
		$condition = array (
				'week_num' => $week_num,
				'user_id' => $id,
				'term_id' => $term ['id'] 
		);
		$selectClass = $this->courseModel->getCurrentWeekCourse ( $condition );
		$sections = $this->courseModel->getSections ();
		$courses = $this->courseModel->getCourses ();
		$grades = $this->courseModel->getGrade ();
		
		// 获取所有已排课的周
		$hasCourseWeeks = $this->courseModel->getHasCourseWeeks ( array (
				'user_id' => $id,
				'term_id' => $term ['id'] 
		) );
		
		// ajax 返回时需要处理课程名称
		foreach ( $selectClass as &$class ) {
			foreach ( $courses as $course ) {
				if ($class ['course_id'] == $course ['id']) {
					$class ['class_name'] = $course ['class_name'];
					break;
				}
			}
		}
		// 增加日期
		$courseDate [0] = $stime1;
		$courseDate [1] = $stime2;
		$courseDate [2] = $stime3;
		$courseDate [3] = $stime4;
		$courseDate [4] = $stime5;
		$courseDate [5] = $stime6;
		$courseDate [6] = $stime7;
		
		$this->assign ( 'sections', $sections );
		$this->assign ( 'selectClass', $selectClass );
		$this->assign ( 'courses', $courses );
		$this->assign ( 'grades', $grades );
		$this->assign ( 'termName', $term ['name'] );
		$this->assign ( 'week_num', $week_num );
		$this->assign ( 'stime1', $stime1 );
		$this->assign ( 'stime2', $stime2 );
		$this->assign ( 'stime3', $stime3 );
		$this->assign ( 'stime4', $stime4 );
		$this->assign ( 'stime5', $stime5 );
		$this->assign ( 'stime6', $stime6 );
		$this->assign ( 'stime7', $stime7 );
		$this->assign ( 'weeks', $weeks );
		$this->assign ( 'hasCourseWeeks', $hasCourseWeeks );
		$this->assign ( 'otherId', $id );
		$this->assign ( 'isAdmin', CheckPermission ( 'dreambox_normal', 'modify_school_course' ) );
		if ($_POST ['week_num']) {
			foreach ( $selectClass as &$class ) {
				foreach ( $grades as $grade ) {
					if ($class ['grade_id'] == $grade ['id']) {
						$class ['grade_name'] = $grade ['name'];
						break;
					}
				}
			}
			$this->ajaxReturn ( $selectClass, json_encode ( $courseDate ) );
		} else {
			$this->display ( 'previewCourse' );
		}
	}
}