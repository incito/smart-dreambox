<?php
class CourseManageAction extends Action {
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
	public function index() {
		if ($this->_checkPermission ()) {
			$this->display ( 'index' );
		} else {
			$this->error ( '你没权限批量排课' );
		}
	}
	public function template() {
		if ($this->_checkPermission ()) {
			$term = model ( 'Term' )->getCurrentTerm ( $this->mid );
			if (! $term) {
				$this->error ( '你还未创建学期' );
			}
			// 载入Excel操作类
			require_once ADDON_PATH . '/library/Excel.class.php';
			$file_path = DATA_PATH . '/excel/template/course_template.xlsx';
			// 设置缓存方式
			// $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_memcache;
			// $cacheSettings = array( 'memcacheServer' => C('MEMCACHE_HOST'),
			// 'memcachePort' => C('MEMCACHE_PORT'),
			// 'cacheTime' => 30
			// );
			// PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
			$objExcel = Excel::read ( $file_path );
			$objExcel->getProperties ()->setCreator ( "yingl" )->setLastModifiedBy ( "yingl" )->setTitle ( "批量选课" )->setSubject ( "批量选课" )->setDescription ( "批量选课模板" )->setKeywords ( "office excel PHPExcel" )->setCategory ( "批量选课" );
			$objExcel->setActiveSheetIndex ( 0 );
			$excel = new Excel ();
			$config ['allowBlank'] = true;
			$section = array_keys ( $this->sectionArray );
			$week = array_keys ( $this->weekArray );
			$maxWeek = M ()->query ( 'select max(week_num) as week from ts_db_week where term_id=' . $term ['id'] );
			$weeks = $this->_getWeeks ( $maxWeek [0] ['week'] );
			$courses = M ( 'Course' )->field ( 'class_name' )->where ( 'is_close=0 and is_del=0' )->findAll ();
			$courses = getSubByKey ( $courses, 'class_name' );
			$grades = M ( 'db_grade' )->field ( 'name' )->findAll ();
			$grades = getSubByKey ( $grades, 'name' );
			for($i = 0; $i < 100; $i ++) {
				// 设置节数
				$excel->setList ( $objExcel, 'A' . ($i + 2), $section, $config );
				// 设置星期几
				$excel->setList ( $objExcel, 'B' . ($i + 2), $week, $config );
				// 开始周
				$excel->setList ( $objExcel, 'C' . ($i + 2), $weeks, $config );
				// 结束周
				$excel->setList ( $objExcel, 'D' . ($i + 2), $weeks, $config );
				// 课程
				$excel->setList ( $objExcel, 'E' . ($i + 2), $courses, $config );
				// 年级
				$excel->setList ( $objExcel, 'H' . ($i + 2), $grades, $config );
			}
			// 输出
			header ( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;charset=GB2312' );
			header ( 'Content-Disposition: attachment;filename=' . iconv ( 'UTF-8', 'GB2312', '批量排课模板' ) . '.xlsx' );
			header ( 'Cache-Control: max-age=0' );
			$objWriter = PHPExcel_IOFactory::createWriter ( $objExcel, 'Excel2007' );
			$objWriter->save ( 'php://output' ); // 这里生成excel后会弹出下载
		} else {
			$this->error ( '你没有权限!' );
		}
	}
	/**
	 * 上传课程
	 */
	public function uploadCourse() {
		$term = model ( 'Term' )->getCurrentTerm ( $this->mid );
		if (! $term) {
			$this->error ( '你还未创建学期' );
		}
		if (! $this->_checkPermission ()) {
			$this->error ( '对不起，您没有权限进行该操作！' );
		}
		$model = M ( 'CourseManage' );
		
		$excel = $model->readCourseFile ( $_FILES ['upload'] ['tmp_name'] );
		
		if (! $excel) {
			$this->error ( '文件格式不正确，导入失败' );
		} else {
			$model->term = $term ;
			$model->uid = $this->mid;
			// 导入excel
			$model->importExcel ( $excel );
			$data = $model->errorLog;
			$this->ajaxReturn ( $data, null, $data ? 0 : 1 );
		}
	}
	/**
	 * 下载日志
	 */
	public function downLog() {
		$start = $_REQUEST ['beginNum'];
		$data = M ()->query ( 'SELECT * FROM ts_db_course_log WHERE operate_num = ( SELECT MAX(operate_num) FROM ts_db_course_log ) LIMIT ' . $start . ',10' );
		$this->ajaxReturn ( $data );
	}
	
	/**
	 * 权限校验
	 *
	 * @return boolean
	 */
	private function _checkPermission() {
		return CheckPermission ( 'dreambox_normal', 'modify_school_course' );
	}
	private function _getWeeks($max) {
		$weeks = array ();
		for($i = 1; $i <= $max; $i ++) {
			$weeks [] = $i;
		}
		return $weeks;
	}
}