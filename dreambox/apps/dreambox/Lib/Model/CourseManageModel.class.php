<?php
class CourseManageModel extends Model {
	public $operateNum = 0;
	public $uid;
	public $term;
	public $errorLog = array ();
	private $columnArray;
	private $excelCourseArray = array ();
	private $startWeek;
	private $endWeek;
	private $uidArray = array ();
	private $realNameArray = array ();
	private $errorUser = array ();
	private $sectionArray = array (
			'第一节' => 1,
			'第二节' => 2,
			'第三节' => 3,
			'第四节' => 4,
			'第五节' => 5,
			'第六节' => 6,
			'第七节' => 7,
			'第八节' => 8 
	);
	private $weekArray = array (
			'星期一' => 1,
			'星期二' => 2,
			'星期三' => 3,
			'星期四' => 4,
			'星期五' => 5,
			'星期六' => 6,
			'星期日' => 0 
	);
	private $gradeArray = array (
			'一年级' => 1,
			'二年级' => 2,
			'三年级' => 3,
			'四年级' => 4,
			'五年级' => 5,
			'六年级' => 6,
			'七年级' => 7,
			'八年级' => 8,
			'九年级' => 9 
	);
	private $columnNameArray = array (
			'A' => '第几节',
			'B' => '星期几',
			'C' => '开始周',
			'D' => '结束周',
			'E' => '课程名称',
			'F' => '教师身份证',
			'G' => '教师姓名',
			'H' => '上课年级',
			'I' => '上课班级' 
	);
	private $courseArray;
	private $logIndex = 1;
	private $row;
	private $column;
	private $courseModel;
	/**
	 * 初始化，配置内容标题
	 *
	 * @return void
	 */
	public function _initialize() {
		$this->errorLog = array ();
		$this->courseModel = M ( 'Course' );
		$courses = $this->courseModel->getCourses ();
		$names = getSubByKey ( $courses, 'class_name' );
		$pattern = '/^(' . implode ( '|', $names ) . ')$/';
		$this->columnArray = array (
				'A' => '/^第(一|二|三|四|五|六|七|八)节$/',
				'B' => '/^星期(一|二|三|四|五|六|日)$/',
				'C' => '/^[1-9][0-9]?$/',
				'D' => '/^[1-9][0-9]?$/',
				'E' => $pattern,
				'F' => '/^.{15,18}$/',
				'G' => '/^.{1,30}$/',
				'H' => '/^(一|二|三|四|五|六|七|八|九)年级$/',
				'I' => '/^[1-9][0-9]?$/' 
		);
		foreach ( $courses as $course ) {
			$this->courseArray [$course ['class_name']] = $course ['id'];
		}
	}
	/**
	 * 读取excel
	 */
	public function readCourseFile($data) {
		// 载入Excel操作类
		require_once ADDON_PATH . '/library/Excel.class.php';
		
		$excel = new Excel ();
		return $excel->read ( $data );
		// return Excel::readForUpload ( $data, $_SESSION ['mid'] );
	}
	/**
	 * 导入excel
	 */
	public function importExcel($excel) {
		// 记录当前操作是第几次
		$this->setOperateNum ();
		// $this->addLog ( '文件上传成功，开始解析......' );
		// 封装excel数据
		$this->fillExcelData ( $excel );
		// $this->addLog ( 'excel数据校验数据完成' );
		// $this->addLog ( '开始导入课表数据......' );
		// 导入课表
		// 消息提示不需要提示列名
		unset ( $this->column );
		$this->importCourses ();
		// $this->addLog ( '导入完成......' );
	}
	/**
	 * 封装excel数据
	 */
	private function fillExcelData($excel) {
		$currentSheet = $excel->getSheet ( 0 ); // 第一个工作簿
		$allRow = $currentSheet->getHighestRow (); // 行数
		                                           // 封装excel 数据
		for($this->row = 2; $this->row <= $allRow; $this->row ++) {
			// 检查每一行数据是否有异常
			if ($this->checkRow ( $currentSheet )) {
				for($this->startWeek; $this->startWeek <= $this->endWeek; $this->startWeek ++) {
					$this->fillExcelCourseArray ( $currentSheet, $this->startWeek );
				}
			}
		}
	}
	public function setOperateNum() {
		$result = M ()->query ( 'SELECT operate_num FROM ts_db_course_log order by id desc limit 1' );
		if ($result) {
			$this->operateNum = $result [0] ['operate_num'] + 1;
		} else {
			$this->operateNum = 1;
		}
	}
	private function addLog($msg) {
		$data ['content'] = $this->getLog ( $msg );
		$data ['term_id'] = $this->term ['id'];
		$data ['user_id'] = $this->uid;
		$data ['ctime'] = time ();
		$data ['operate_num'] = $this->operateNum;
		array_push ( $this->errorLog, array (
				'content' => $data ['content'] 
		) );
		M ( 'db_course_log' )->add ( $data );
	}
	private function checkRow($currentSheet) {
		if ($this->emptyLine ( $currentSheet )) {
			return false;
		}
		$result = true;
		foreach ( $this->columnArray as $column => $pattern ) {
			$value = $currentSheet->getCell ( $column . $this->row )->getValue ();
			if (! preg_match ( $pattern, trim ( $value ) )) {
				$this->column = $column;
				$this->addLog ( '格式不正确或不属于指定值' );
				$result = false;
			}
		}
		if ($result) {
			$this->startWeek = intval ( $currentSheet->getCell ( 'C' . $this->row )->getValue () );
			$this->endWeek = intval ( $currentSheet->getCell ( 'D' . $this->row )->getValue () );
			if ($this->endWeek < $this->startWeek) {
				$this->column = 'D';
				$this->addLog ( '结束周不能小于开始周' );
				$result = false;
			}
		}
		
		return $result;
	}
	private function checkValueIsNull($value) {
		return $value == null || trim ( $value ) == '';
	}
	private function getCellValue($currentSheet, $column) {
		return trim ( $currentSheet->getCell ( $column . $this->row )->getValue () );
	}
	private function fillExcelCourseArray($currentSheet, $week) {
		$idCard = $this->getCellValue ( $currentSheet, 'F' );
		$userName = $this->getCellValue ( $currentSheet, 'G' );
		// 判断当前用户是否存在
		$uid = $this->getUid ( $idCard, $userName );
		if (! $uid) {
			return;
		}
		$sectionName = $this->getCellValue ( $currentSheet, 'A' );
		$sectionNum = $this->sectionArray [$sectionName];
		$weekDayName = $this->getCellValue ( $currentSheet, 'B' );
		$weekDay = $this->weekArray [$weekDayName];
		$courseName = $this->getCellValue ( $currentSheet, 'E' );
		$courseId = $this->courseArray [$courseName];
		$gradeName = $this->getCellValue ( $currentSheet, 'H' );
		$gradeId = $this->gradeArray [$gradeName];
		$classNum = $this->getCellValue ( $currentSheet, 'I' );
		
		// 是否重复
		$flag = false;
		foreach ( $this->excelCourseArray as &$course ) {
			// 如果是同一老师，并且星期几 第几周 第几节完全一样，则替换
			if ($course ['user_id'] == $uid && $course ['section_num'] == $sectionNum && $course ['week_day'] == $weekDay && $course ['week_num'] == $week) {
				$course ['grade_id'] = $gradeId;
				$course ['class_num'] = $classNum;
				$course ['course_id'] = $courseId;
				$course ['row'] = $this->row;
				$flag = true;
				break;
			} else if ($course ['section_num'] == $sectionNum && $course ['week_day'] == $weekDay && $course ['week_num'] == $week && $course ['grade_id'] == $gradeId && $course ['class_num'] == $classNum) {
				$course ['user_id'] = $uid;
				$course ['course_id'] = $courseId;
				$course ['row'] = $this->row;
				$flag = true;
				break;
			}
		}
		if (! $flag) {
			// 判断时间是否合法
			$check = $this->courseModel->checkDate ( $week, $weekDay, $this->term );
			if ($check) {
				$this->addLog ( '第' . $week . '周' . $weekDayName . "不在学期(" . date ( "Y-m-d", $this->term ["stime"] ) . '~' . date ( "Y-m-d", $this->term ["etime"] ) . ")范围内" );
				return;
			}
			$data ['user_id'] = $uid;
			$data ['course_id'] = $courseId;
			$data ['week_num'] = $week;
			$data ['week_day'] = $weekDay;
			$data ['section_num'] = $sectionNum;
			$data ['grade_id'] = $gradeId;
			$data ['class_num'] = $classNum;
			$data ['feedback_status'] = 0;
			$data ['term_id'] = $this->term ['id'];
			$data ['row'] = $this->row;
			array_push ( $this->excelCourseArray, $data );
		}
	}
	private function importCourses() {
		// 删除未签到课程
		$condition ['feedback_status'] = 0;
		$condition ['term_id'] = $this->term ['id'];
		M ( 'db_select_course' )->where ( $condition )->delete ();
		// 查询出 剩余数据
		$courseModel = M ( 'db_select_course' );
		$courses = $courseModel->where ( array (
				'term_id' => $this->term ['id'] 
		) )->select ();
		// 遍历需要导入的数据
		foreach ( $this->excelCourseArray as &$excelCourse ) {
			$this->row = $excelCourse ['row'];
			// 如果不重复 则添加课程
			if (! $this->hasRepeatCourse ( $courses, $excelCourse )) {
				unset ( $excelCourse ['row'] );
				$courseModel->add ( $excelCourse );
			}
		}
	}
	private function getUid($idCard, $name) {
		// 如果当前用户身份证或名称不正确
		if (in_array ( $idCard . '_' . $name, $this->errorUser )) {
			return 0;
		}
		$uid = $this->uidArray [$idCard . '_' . $name];
		if (! $uid) {
			$condition ['idcard'] = $idCard;
			$condition ['realname'] = $name;
			$condition ['sid'] = $this->term ['school_id'];
			$uid = M ( 'user_verified' )->where ( $condition )->getField ( 'uid' );
			if ($uid) {
				$this->uidArray [$idCard . '_' . $name] = $uid;
			} else {
				array_push ( $this->errorUser, $idCard . '_' . $name );
				$this->addLog ( '身份证：' . $idCard . ' 姓名：' . $name . '，该用户不存在或不是该学校的梦想老师' );
			}
		}
		return $uid;
	}
	private function getRealName($uid) {
		if (! $this->realNameArray [$uid]) {
			$realName = M ( 'user_verified' )->where ( 'uid=' . $uid )->getField ( 'realname' );
			$this->realNameArray [$uid] = $realName;
		}
		return $this->realNameArray [$uid];
	}
	private function hasRepeatCourse($courses, $excelCourse) {
		foreach ( $courses as $co ) {
			if ($co ['feedback_status'] == 0) {
				continue;
			}
			// 同一班级
			$flag1 = $excelCourse ['grade_id'] == $co ['grade_id'] && $excelCourse ['class_num'] == $co ['class_num'];
			// 同一用户
			$flag2 = $excelCourse ['user_id'] == $co ['user_id'];
			// 同一课程
			$flag3 = $excelCourse ['course_id'] == $co ['course_id'];
			// 相同时间
			$flag4 = $excelCourse ['section_num'] == $co ['section_num'] && $excelCourse ['week_day'] == $co ['week_day'] && $excelCourse ['week_num'] == $co ['week_num'];
			// 相同时间 相同地点 不同老师
			if ($flag1 && $flag4 && ! $flag2) {
				$this->addLog ( '第' . $excelCourse ['week_num'] . '周' . array_search ( $excelCourse ['week_day'], $this->weekArray ) . array_search ( $excelCourse ['section_num'], $this->sectionArray ) . '已被' . $this->getRealName ( $co ['user_id'] ) . '老师签到了' . array_search ( $co ['course_id'], $this->courseArray ) );
				return true;
			} else if ($flag2 && $flag4 && ! $flag1) { // 相同老师 相同时间 不同地点
				$this->addLog ( $this->getRealName ( $excelCourse ['user_id'] ) . '老师在第' . $excelCourse ['week_num'] . '周' . array_search ( $excelCourse ['week_day'], $this->weekArray ) . array_search ( $excelCourse ['section_num'], $this->sectionArray ) . '已为' . array_search ( $co ['grade_id'], $this->gradeArray ) . $co ['class_num'] . '班签到了' . array_search ( $co ['course_id'], $this->courseArray ) );
				return true;
			} else if ($flag1 && $flag4 && $flag2 && ! $flag3) { // 相同时间 相同老师 相同地点 不同课程
				$this->addLog ( $this->getRealName ( $excelCourse ['user_id'] ) . '老师在第' . $excelCourse ['week_num'] . '周' . array_search ( $excelCourse ['week_day'], $this->weekArray ) . array_search ( $excelCourse ['section_num'], $this->sectionArray ) . '已签到' . array_search ( $co ['course_id'], $this->courseArray ) );
				return true;
			}
		}
		return false;
	}
	private function getLog($msg) {
		$log = ($this->logIndex ++) . "&nbsp;出错位置：" . $this->row . '行&nbsp;';
		if ($this->column) {
			$log .= $this->column . '列&nbsp;';
		}
		$columnName = $this->columnNameArray [$this->column];
		$log .= $columnName;
		$log .= '</p><p>';
		for($i = 0; $i <= strlen ( $this->logIndex ) + 1; $i ++) {
			$log .= '&nbsp;';
		}
		$log .= ('出错信息：' . $msg);
		return $log;
	}
	private function emptyLine($currentSheet) {
		foreach ( $this->columnNameArray as $key => $value ) {
			if (! $this->checkValueIsNull ( $this->getCellValue ( $currentSheet, $key ) )) {
				return false;
			}
		}
		return true;
	}
}