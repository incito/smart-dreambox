<?php

/**
 * 梦想老师课程签到管理
 * @author zjj
 *
 */
class LessonFeedbackAction extends Action {
	private $week_array = array (
			1 => '周一',
			2 => '周二',
			3 => '周三',
			4 => '周四',
			5 => '周五',
			6 => '周六',
			0 => '周日' 
	);
	
	/**
	 * 查询可签到数据
	 */
	public function show() {
		$uid = $this->mid;
		$this->assign ( 'uid', $uid );
		
		$realname = M ( "UserVerified" )->getRealname ( $uid );
		$this->assign ( 'realname', $realname );
		
		$courseModel = M ( "Course" );
		// 课节
		$sections = $courseModel->getSections ();
		$this->assign ( 'sections', $sections );
		// 所有课程
		$courses = $courseModel->getCourses ();
		$this->assign ( 'courses', $courses );
		// 所有年级
		$grades = $courseModel->getGrade ();
		$this->assign ( 'grades', $grades );
		
		$term = model ( 'Term' )->getCurrentTerm ( $uid );
		$stime = $term ['stime'];
		$this->assign ( 'term_start_date', date ( 'Y,m,d', $stime ) );
		$this->assign ( 'term_end_date', date ( 'Y,m,d', $term ['etime'] ) );
		
		$data = $courseModel->queryNeedSignCourse ( $uid );
		foreach ( $data as & $course ) {
			$lessonTime = $courseModel->getTimeStr ( $stime, $course ['week_num'], $course ['week_day'] );
			$course ['lessonTime'] = $lessonTime;
			$course ['day_of_week'] = $this->week_array [$course ['week_day']];
			
			$cour = $courseModel->getCourseById ( $course ['course_id'] );
			$course ['course_name'] = $cour ['class_name'];
			
			$grade = $courseModel->getGradeById ( $course ['grade_id'] );
			$course ['grade_name'] = $grade ['name'];
			
			$section = $courseModel->getSectionById ( $course ['section_num'] );
			$course ['section_name'] = $section ['name'];
			// 课程章节
			$hours = $courseModel->getHours ( $course ['course_id'] );
			$course ['hours'] = $hours;
		}
		// echo var_dump($data);
		
		$this->assign ( 'courseData', $data );
		
		$this->display ();
	}
	
	/**
	 * 判断是否有需要签到的数据
	 */
	public function needFeedback() {
		if ($_SESSION ['SHOW_FEEDBACK_POP'] == 'NO') {
			exit ( json_encode ( array (
					'code' => 0 
			) ) );
		}
		// 学校管理员弹出确认签到
		if (CheckPermission ( 'dreambox_normal', 'confirm_feed' )) {
			$data = M ( 'LessonFeedback' )->getConfirmList ( $this->mid, false );
			if (empty ( $data )) {
				exit ( json_encode ( array (
						'code' => 0 
				) ) );
			}
			//学期列表
			$terms=M('Term')->where('school_id=(select sid from ts_user_verified where uid='.$this->mid.' limit 1) order by etime desc limit 3')->findAll();
			$this->assign ( 'confirm_list', $data );
			$this->assign ( 'term_list', $terms );
			$tmp = $this->fetch ( 'confirm' );
			exit ( json_encode ( array (
					'code' => 2,
					'data' => $tmp 
			) ) );
		}
		// 梦想老师弹出签到提示
		if (! CheckPermission ( 'dreambox_normal', 'select_class' )) {
			exit ( json_encode ( array (
					'code' => 0 
			) ) );
		}
		$uid = $this->mid;
		$data = M ( "Course" )->queryNeedSignCourse ( $uid );
		if ($data) {
			exit ( json_encode ( array (
					'code' => 1 
			) ) );
		} else {
			exit ( json_encode ( array (
					'code' => 0 
			) ) );
		}
	}
	
	/**
	 * 下次再提示签到
	 */
	public function nextFeedback() {
		$_SESSION ['SHOW_FEEDBACK_POP'] = 'NO';
	}
	
	/**
	 * 签到
	 */
	public function feedback() {
		// 检测是否有其他账号已经登陆
		$uid = $_POST ['uid'];
		if ($uid != $this->mid) {
			echo - 1;
			return;
		}
		
		$failed_count = 0;
		$success_count = 0;
		$json = json_decode ( $_POST ['jsonarray'] );
		$integral = 0;
		
		$error = '';
		foreach ( $json as $param ) {
			$data ['course_id'] = $param->sc_id;
			$data ['term_id'] = $param->term_id;
			$data ['course_name'] = $param->course_name;
			$data ['grade_name'] = $param->grade_name;
			$data ['class_name'] = $param->class_name;
			$data ['section_num'] = $param->section_num;
			$data ['hours_name'] = $param->hours_name;
			$data ['lesson_time'] = $param->time;
			$status = M ( "LessonFeedback" )->feedback ( $this->mid, $data );
			if (! is_int ( $status )) {
				$error = $error . $status . ',';
				$failed_count ++;
			} else {
				$success_count ++;
				$integral += $status;
			}
		}
		// 获取需要签到的数量
		// $courseModel = D ( 'Course', 'dreambox' );
		// $signCount = count ( $courseModel->queryNeedSignCourse ( $this->mid ) );
		$res = array (
				'error' => $error,
				'failed_count' => $failed_count,
				'success_count' => $success_count,
				// 'sign_count' => $signCount,
				'integral' => $integral 
		);
		$this->ajaxReturn ( $res );
	}
	
	/**
	 * 签到页面修改选课
	 */
	public function updateCourse() {
		$data ['id'] = $_POST ['id'];
		$data ['time'] = $_POST ['time'];
		
		$term = model ( 'Term' )->getCurrentTerm ( $this->mid );
		// $diff_time = strtotime($data['time']) - strtotime($term['stime']);
		$data ['week_num'] = week_count ( $term ['stime'], strtotime ( $data ['time'] ) );
		$data ['week_day'] = date ( 'w', strtotime ( $data ['time'] ) );
		
		$data ['grade_id'] = $_POST ['grade'];
		$data ['class_num'] = $_POST ['class'];
		$data ['section_num'] = $_POST ['section'];
		$data ['course_id'] = $_POST ['course'];
		
		$courseModel = M ( "Course" );
		$isRepeat = $courseModel->selectRepeatCourse(null, $term['id'], $data);
		if(!empty($isRepeat) && $isRepeat[0]['id'] != $data ['id']){
		    $result['status'] = 0;
		    $result['msg'] = "该班级在此时间段已经有排课，请重新输入！";
		    $this->ajaxReturn ( $result );
		    return;
		}
		
		$courseModel->updateCourse ( $data );
		
		// $grade = $courseModel->getGradeById($data['grade_id']);
		// $result['grade_name'] = $grade['name'];
		
		// $result['lessonTime'] = $data['time'];
		$result ['day_of_week'] = $this->week_array [$data ['week_day']];
		
		// $cour = $courseModel->getCourseById($data['course_id']);
		// $result['course_name' ] = $cour['class_name'];
		
		// $section = $courseModel->getSectionById($data['section_num']);
		// $result['section_name'] = $section['name'];
		
		// 判断选择的时间是否大于当前时间
		if (strtotime ( $data ['time'] ) > strtotime ( date ( "Y-m-d" ) )) {
			$result ['gt_now'] = 1;
		} else {
			$result ['gt_now'] = 0;
		}
		
		// 重新查询课程的所有章节
		$result ['hours'] = $courseModel->getHours ( $data ['course_id'] );
		//执行成功的状态
		$result['status'] = 1;
		$this->ajaxReturn ( $result );
	}
	/**
	 * 获得待确认签到列表
	 */
	public function confirm_list() {
		// 权限检测
		if (CheckPermission ( 'dreambox_normal', 'confirm_feed' )) {
			//请求类型 0：数据 1：页面;
			$view=intval($_POST['view']);
			$type = t ( $_POST ['type'] );
			$terms = t ( $_POST ['terms'] );
			if (strpos ( $type, '0' ) !== false) {
				$status [] = '0';
			}
			if (strpos ( $type, '1' ) !== false) {
				$status [] = '1';
			}
			if (strpos ( $type, '2' ) !== false) {
				$status [] = '2';
			}
			//过滤非全覆盖状态
			if($status&&count($status)<3){
				$param='lf.status in ('.implode(',', $status).')';
			}
			//过滤学期
			if($terms){
				$param.=($param?' and ':'').'term_id in('. $terms.')';
			}
			//待确认列表
			$data = M ( 'LessonFeedback' )->getConfirmList ( $this->mid, $param);
			if($view==1){
				//学期列表
				$terms=M('Term')->where('school_id=(select sid from ts_user_verified where uid='.$this->mid.' limit 1) order by etime desc limit 3')->findAll();
				$this->assign('confirm_list',$data);
				$this->assign('term_list',$terms);
				exit($this->fetch('confirm'));
			}
			exit(json_encode(array('status'=>1,'data'=>$data)));
		} else {
			exit ( 0 );
		}
	}
	/**
	 * 确认签到
	 */
	public function confirm() {
		// 权限检测
		if (CheckPermission ( 'dreambox_normal', 'confirm_feed' )) {
			$confirm_ids = t ( $_POST ['id1'] ); // 确认ID
			$back_ids = t ( $_POST ['id2'] ); // 驳回ID
			$ret = M ( 'LessonFeedback' )->confirm1 ( $confirm_ids, $back_ids, $this->mid );
			echo $ret ? 1 : 0;
		} else {
			$this->error ( '你没有该操作权限' );
		}
	}
	/**
	 * 不签到
	 */
	public function removeCourse() {
		$data ['feedback_status'] = - 1;
		M ( "Course" )->removeCourse ( $_POST ['id'], $data );
	}
}
?> 