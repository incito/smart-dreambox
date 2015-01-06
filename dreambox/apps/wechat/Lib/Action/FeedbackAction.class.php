<?php
class FeedbackAction extends Action {
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
	 * 查看待签到记录
	 */
	public function querySelectCourse() {
		$raw_post_data = file_get_contents ( 'php://input', 'r' );
		$params = json_decode ( $raw_post_data );
		$openid = $params->openId;
		$uid = getUidByOid ( $openid );
		if (! $uid) {
			$result ['code'] = 2;
			$result ['msg'] = "您还没登陆";
			exit ( json_encode ( $result ) );
		}
		
		try {
			$courseModel = M ( "Course", "dreambox" );
			$termModel = M ( "Term", "dreambox" );
			$term = $termModel->getCurrentTerm ( $uid );
			$stime = $term ['stime'];
			
			$data = $courseModel->queryNeedSignCourse ( $uid );
			foreach ( $data as & $course ) {
				$lessonTime = $courseModel->getTimeStr ( $stime, $course ['week_num'], $course ['week_day'] );
				$course ['sc_id'] = $course ['id'];
				$course ['lesson_date'] = $lessonTime;
				$course ['week_day'] = $this->week_array [$course ['week_day']];
				
				$cour = $courseModel->getCourseById ( $course ['course_id'] );
				$course ['course_name'] = $cour ['class_name'];
				
				$grade = $courseModel->getGradeById ( $course ['grade_id'] );
				$course ['grade_name'] = $grade ['name'];
				
				$section = $courseModel->getSectionById ( $course ['section_num'] );
				$course ['section_name'] = $section ['name'];
			}
			
			$result ['code'] = 1;
			$result ['data'] = $data;
		} catch ( Exception $e ) {
			$result ['code'] = 0;
			$result ['msg'] = "操作失败[" . $e . "]";
		}
		echo json_encode ( $result );
	}
	
	/**
	 * 查询一门主课程的所有课时
	 */
	public function queryClassHour() {
		$raw_post_data = file_get_contents ( 'php://input', 'r' );
		$param = json_decode ( $raw_post_data );
		$sc_id = $param->sc_id;
		
		try {
			// 课程章节
			$hours = M ( "Course", "dreambox" )->getHours ( $sc_id );
			foreach ( $hours as & $hour ) {
				$hour ['ch_id'] = $hour ['id'];
			}
			$result ['code'] = 1;
			$result ['data'] = $hours;
		} catch ( Exception $e ) {
			$result ['code'] = 0;
			$result ['msg'] = "操作失败[" . $e . "]";
		}
		echo json_encode ( $result );
	}
	
	/**
	 * 删除选课（这节课我没上）
	 */
	public function deleteSC() {
		try {
			$raw_post_data = file_get_contents ( 'php://input', 'r' );
			$param = json_decode ( $raw_post_data );
			$sc_id = $param->sc_id;
			
			$data ['feedback_status'] = - 1;
			M ( "Course", "dreambox" )->removeCourse ( $sc_id, $data );
			
			$result ['code'] = 1;
		} catch ( Exception $e ) {
			$result ['code'] = 0;
			$result ['msg'] = "操作失败[" . $e . "]";
		}
		echo json_encode ( $result );
	}
	
	/**
	 * 提交签到记录
	 */
	public function save() {
		$raw_post_data = file_get_contents ( 'php://input', 'r' );
		$params = json_decode ( $raw_post_data );
		$openid = $params->openId;
		$signData = $params->saveData;
		$uid = getUidByOid ( $openid );
		
		$failed_count = 0;
		$success_count = 0;
		$integral = 0;
		$feedIds = array ();
		$error = '';
		$feedModel = M ( "LessonFeedback" );
		$couponModel = M ( 'Coupon' );
		foreach ( $signData as $param ) {
			$data ['course_id'] = $param->sc_id;
			$data ['term_id'] = $param->term_id;
			$data ['course_name'] = $param->course_name;
			$data ['grade_name'] = $param->grade_name;
			$data ['class_name'] = $param->class_name;
			$data ['section_num'] = $param->section_num;
			$data ['hours_name'] = $param->hours_name;
			$data ['lesson_time'] = $param->time;
			$data ['from'] = '1';
			$status = $feedModel->feedback ( $uid, $data );
			if (! is_int ( $status )) {
				$error = $error . $status . ',';
				$failed_count ++;
			} else {
				$success_count ++;
				$integral += $status;
				$feedIds [] = $feedModel->getFeedId ();
			}
		}
		// 发放红包
		$coupon = M ( 'Coupon' )->sendAfterSign ( $uid, $feedIds );
		if ($failed_count > 0) {
			if ($success_count > 0) {
				$result ['code'] = 1;
				$result ['msg'] = "签到成完成，$failed_count条失败";
				$result ['data'] = array (
						'integral' => $integral,
						'money' => $coupon ['amount'] 
				);
			} else {
				$result ['code'] = 0;
				$result ['msg'] = "签到失败";
			}
		} else {
			$result ['code'] = 1;
			$result ['msg'] = "签到成功";
			$result ['data'] = array (
					'integral' => $integral,
					'money' => $coupon ['amount'] / 100 
			);
		}
		
		echo json_encode ( $result );
	}
	
	/**
	 * 提交实时签到记录
	 */
	public function nowSign() {
		$raw_post_data = file_get_contents ( 'php://input', 'r' );
		$params = json_decode ( $raw_post_data );
		$openid = $params->openId;
		$signData = $params->saveData;
		$uid = getUidByOid ( $openid );
		
		$failed_count = 0;
		$success_count = 0;
		$integral = 0;
		$feedIds = array ();
		$error = '';
		$feedModel = M ( "LessonFeedback" );
		$couponModel = M ( 'Coupon' );
		foreach ( $signData as $param ) {
			$data ['course_id'] = $param->sc_id;
			$data ['term_id'] = $param->term_id;
			$data ['course_name'] = $param->course_name;
			$data ['grade_name'] = $param->grade_name;
			$data ['class_name'] = $param->class_name;
			$data ['section_num'] = $param->section_num;
			$data ['hours_name'] = $param->hours_name;
			$data ['lesson_time'] = $param->time;
			$data ['from'] = '1';
			$status = $feedModel->feedback ( $uid, $data, 'now_sign' );
			if (! is_int ( $status )) {
				$error = $error . $status . ',';
				$failed_count ++;
			} else {
				$success_count ++;
				$integral += $status;
				$feedIds [] = $feedModel->getFeedId ();
			}
		}
		// 发放红包
		$coupon = M ( 'Coupon' )->sendAfterSign ( $uid, $feedIds );
		if ($failed_count > 0) {
			if ($success_count > 0) {
				$result ['code'] = 1;
				$result ['msg'] = "签到成完成，$failed_count条失败";
				$result ['data'] = array (
						'integral' => intval ( $integral ),
						'money' => intval ( $coupon ['amount'] ) 
				);
			} else {
				$result ['code'] = 0;
				$result ['msg'] = "签到失败";
			}
		} else {
			$result ['code'] = 1;
			$result ['msg'] = "签到成功";
			$diff = M ()->query ( 'select a.value-b.value diff from ts_db_credit_setting a,ts_db_credit_setting b where a.name="now_sign" AND b.name="sign"' );
			$result ['data'] = array (
					'integral' => intval ( $integral ),
					'diff' => intval ( $diff [0] ['diff'] ),
					'money' => $coupon ['amount'] / 100 
			);
		}
		
		echo json_encode ( $result );
	}
}