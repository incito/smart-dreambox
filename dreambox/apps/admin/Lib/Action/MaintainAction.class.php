<?php

/**
 * 签到后台管理
 * @author zjj
 *
 */
// 加载后台控制器
tsload ( APPS_PATH . '/admin/Lib/Action/AdministratorAction.class.php' );
class MaintainAction extends AdministratorAction {
	private $top;
	private $type;
	/**
	 * 初始化
	 */
	public function _initReport() {
		$this->pageTitle ['index'] = '运维部报表';
		// tab选项
		$this->pageTab [] = array (
				'title' => '学校课表汇总',
				'tabHash' => 'school_course_stat',
				'url' => U ( 'admin/Maintain/school_course_stat' ) 
		);
		$this->pageTab [] = array (
				'title' => '学校反馈汇总',
				'tabHash' => 'school_course_feedback_stat',
				'url' => U ( 'admin/Maintain/school_course_feedback_stat' ) 
		);
		$this->pageTab [] = array (
				'title' => '教师课表汇总',
				'tabHash' => 'teacher_course_stat',
				'url' => U ( 'admin/Maintain/teacher_course_stat' ) 
		);
		$this->pageTab [] = array (
				'title' => '教师反馈汇总',
				'tabHash' => 'teacher_course_feedback_stat',
				'url' => U ( 'admin/Maintain/teacher_course_feedback_stat' ) 
		);
		$this->pageTab [] = array (
				'title' => '梦想课程-课表汇总',
				'tabHash' => 'course_plan',
				'url' => U ( 'admin/Maintain/course_plan' ) 
		);
		$this->pageTab [] = array (
				'title' => '梦想课程-反馈汇总',
				'tabHash' => 'course_feedback',
				'url' => U ( 'admin/Maintain/course_feedback' ) 
		);
		$this->pageTab [] = array (
				'title' => '梦想课程详情-课表汇总',
				'tabHash' => 'course_detail_plan',
				'url' => U ( 'admin/Maintain/course_detail_plan' ) 
		);
		$this->pageTab [] = array (
				'title' => '梦想课程详情-反馈汇总',
				'tabHash' => 'course_detail_feedback',
				'url' => U ( 'admin/Maintain/course_detail_feedback' ) 
		);
	}
	/**
	 * 学校课表汇总
	 */
	public function school_course_stat() {
		$this->school_course_common ();
		$map ['tabHash'] = 'school_course_stat';
		$map ['tableName'] = 'rpt_school_plain';
		$map ['tableFields'] = ', t.school_account, t.teacher_total, t.class_total, t.student_total, t.teacher_num, t.class_num, t.class_count, t.student_num, t.course_time_num, t.course_num, t.teacher_coverage, t.student_coverage, t.class_coverage,s.salon';
		$map ['excelName'] = '学校课表汇总';
		$this->schoolOrTeacherCommon ( $map );
	}
	/**
	 * 学校反馈汇总
	 */
	public function school_course_feedback_stat() {
		$this->school_course_common ();
		$map ['tabHash'] = 'school_course_feedback_stat';
		$map ['tableName'] = 'rpt_school_feedback';
		$map ['tableFields'] = ', t.school_account, t.teacher_total, t.class_total, t.student_total, t.teacher_num, t.class_num, t.class_count, t.student_num, t.course_time_num, t.course_num, t.teacher_coverage, t.student_coverage, t.class_coverage,s.salon';
		$map ['excelName'] = '学校反馈汇总';
		$this->schoolOrTeacherCommon ( $map );
	}
	
	/**
	 * 教师课表汇总
	 */
	public function teacher_course_stat() {
		$this->teacher_course_common ();
		$map ['tabHash'] = 'teacher_course_stat';
		$map ['tableName'] = 'rpt_teacher_course_stat';
		$map ['tableFields'] = ', t.school_email, t.teacher_name, t.teacher_level, t.phone, t.email, t.sex, t.age, t.class_num, t.class_count, t.student_num, t.course_time_num, t.course_num, t.blog_num, t.hot_blog_num, t.valid_integral, t.sum_integral';
		$map ['excelName'] = '教师课表汇总';
		$this->schoolOrTeacherCommon ( $map );
	}
	/**
	 * 教师反馈汇总
	 */
	public function teacher_course_feedback_stat() {
		$this->teacher_course_common ();
		$map ['tabHash'] = 'teacher_course_feedback_stat';
		$map ['tableName'] = 'rpt_teacher_feedback_stat';
		$map ['tableFields'] = ', t.school_email, t.teacher_name, t.teacher_level, t.phone, t.email, t.sex, t.age, t.class_num, t.class_count, t.student_num, t.course_time_num, t.course_num, t.blog_num, t.hot_blog_num, t.valid_integral, t.sum_integral';
		$map ['excelName'] = '教师反馈汇总';
		$this->schoolOrTeacherCommon ( $map );
	}
	
	/**
	 * 梦想课程-课表汇总
	 */
	public function course_plan() {
		$this->course_common ();
		$map ['tabHash'] = 'course_plan';
		$map ['tableName'] = 'rpt_course_plan';
		$map ['tableFields'] = 'term_name, course_name, school_num, teacher_num, class_num, student_num, class_count, course_time_num';
		$map ['excelName'] = '梦想课程-课表汇总';
		$this->termCommon ( $map );
	}
	/**
	 * 梦想课程-反馈汇总
	 */
	public function course_feedback() {
		$this->course_common ();
		$map ['tabHash'] = 'course_feedback';
		$map ['tableName'] = 'rpt_course_feedback';
		$map ['tableFields'] = 'term_name, course_name, school_num, teacher_num, class_num, student_num, class_count, course_time_num';
		$map ['excelName'] = '梦想课程-反馈汇总';
		$this->termCommon ( $map );
	}
	
	/**
	 * 梦想课程详情-课表汇总
	 */
	public function course_detail_plan() {
		$this->course_detail_common ();
		$map ['tabHash'] = 'course_detail_plan';
		$map ['tableName'] = 'rpt_course_detail_plan';
		$map ['tableFields'] = ',t.school_account,t.course_name,t.teacher_name,t.class_num,t.class_count,t.student_num,t.course_time_num';
		$map ['excelName'] = '梦想课程详情-课表汇总';
		$this->schoolOrTeacherCommon ( $map );
	}
	/**
	 * 梦想课程详情-反馈汇总
	 */
	public function course_detail_feedback() {
		$this->course_detail_common ();
		$map ['tabHash'] = 'course_detail_feedback';
		$map ['tableName'] = 'rpt_course_detail_feedback';
		$map ['tableFields'] = ', t.school_account,t.course_name,t.teacher_name,t.class_num,t.class_count,t.student_num,t.course_time_num';
		$map ['excelName'] = '梦想课程详情-反馈汇总';
		$this->schoolOrTeacherCommon ( $map );
	}
	private function school_course_common() {
		$this->pageKeyList = array (
				'term_name',
				'school_number',
				'school_name',
				'school_type',
				'school_nature',
				'region',
				'province',
				'city',
				'area',
				'location',
				'school_account',
				'teacher_total',
				'class_total',
				'student_total',
				'teacher_num',
				'class_num',
				'class_count',
				'student_num',
				'course_time_num',
				'course_num',
				'teacher_coverage',
				'student_coverage',
				'class_coverage',
				'salon' 
		);
		
		$this->searchKey = array (
				'school_name',
				'school_number',
				'term_name' 
		);
		$this->type = 1;
	}
	private function teacher_course_common() {
		$this->pageKeyList = array (
				'term_name',
				'school_number',
				'school_name',
				'school_type',
				'school_nature',
				'region',
				'province',
				'city',
				'area',
				'location',
				'school_email',
				'teacher_name',
				'teacher_level',
				'phone',
				'email',
				'sex',
				'age',
				'class_num',
				'class_count',
				'student_num',
				'course_time_num',
				'course_num',
				'blog_num',
				'hot_blog_num',
				'valid_integral',
				'sum_integral' 
		);
		
		$this->searchKey = array (
				'school_name',
				'school_number',
				'term_name',
				'teacher_name' 
		);
		$this->type = 2;
	}
	private function course_common() {
		$this->pageKeyList = array (
				'term_name',
				'course_name',
				'school_num',
				'teacher_num',
				'class_num',
				'student_num',
				'class_count',
				'course_time_num' 
		);
		
		$this->searchKey = array (
				'term_name',
				'course_name' 
		);
		$this->type = 3;
	}
	private function course_detail_common() {
		$this->pageKeyList = array (
				'term_name',
				'school_number',
				'school_name',
				'school_type',
				'school_nature',
				'region',
				'province',
				'city',
				'area',
				'location',
				'school_account',
				'course_name',
				'teacher_name',
				'class_num',
				'class_count',
				'student_num',
				'course_time_num' 
		);
		
		$this->searchKey = array (
				'school_name',
				'school_number',
				'term_name',
				'teacher_name' 
		);
		$this->type = 4;
	}
	private function schoolOrTeacherCommon($data) {
		$this->_initReport ();
		
		$_REQUEST ['tabHash'] = $data ['tabHash'];
		
		// 查询列表数据
		$listData = M ( "Maintain" )->getSearchData ( $data ['tableName'], $data ['tableFields'] );
		
		$this->pageButton [] = array (
				'title' => '搜索',
				'onclick' => "admin.fold('search_form')" 
		);
		
		$this->pageButton [] = array (
				'title' => '导出excel',
				'onclick' => 'admin.exportExcel(\'' . U ( 'admin/Maintain/exportSchoolOrTeacherTermExcel', array (
						'tableName' => $data ['tableName'],
						'type' => $this->type,
						'tableFields' => $data ['tableFields'],
						'excelName' => $data ['excelName'] 
				) ) . '\')' 
		);
		$this->displayList ( $listData );
	}
	private function termCommon($data) {
		$this->_initReport ();
		
		$_REQUEST ['tabHash'] = $data ['tabHash'];
		
		// 查询列表数据
		$listData = M ( "Maintain" )->getTermData ( $data ['tableName'], $data ['tableFields'] );
		
		$this->pageButton [] = array (
				'title' => '搜索',
				'onclick' => "admin.fold('search_form')" 
		);
		
		$this->pageButton [] = array (
				'title' => '导出excel',
				'onclick' => 'admin.exportExcel(\'' . U ( 'admin/Maintain/exportTermExcel', array (
						'tableName' => $data ['tableName'],
						'type' => $this->type,
						'tableFields' => $data ['tableFields'],
						'excelName' => $data ['excelName'] 
				) ) . '\')' 
		);
		$this->displayList ( $listData );
	}
	/**
	 * 导出excel
	 */
	public function exportSchoolOrTeacherTermExcel() {
		$listData = M ( "Maintain" )->getSearchData ( $_REQUEST ['tableName'], $_REQUEST ['tableFields'], 9999999999 );
		$this->exportExcel ( $listData );
	}
	
	/**
	 * 导出excel
	 */
	public function exportTermExcel() {
		$listData = M ( "Maintain" )->getTermData ( $_REQUEST ['tableName'], $_REQUEST ['tableFields'], 9999999999 );
		$this->exportExcel ( $listData );
	}
	/**
	 * 导出excel
	 */
	public function exportExcel($listData) {
		// 无权限跳转到错误提示页面
		if (! $listData) {
			$this->error ( '暂无数据' );
		}
		// 载入Excel操作类
		require_once ADDON_PATH . '/library/Excel.class.php';
		$excel = new Excel ();
		$_type = $_REQUEST ['type'];
	
	
		if ($_type == 1) {
			$this->top = array (
					'term_name' => '学期',
					'school_number' => '学校代码',
					'school_name' => '学校名称',
					'school_type' => '学校类型',
					'school_nature' => '学校性质',
					'region' => '所属大区',
					'province' => '省/自治区',
					'city' => '市/自治州',
					'area' => '县/区',
					'location' => '学校地址',
					'school_account' => '学校账号',
					'teacher_total' => '教师总数',
					'class_total' => '班级总数',
					'student_total' => '学生总数',
					'teacher_num' => '梦想教师数',
					'class_num' => '梦想班级数',
					'class_count' => '梦想班次数',
					'student_num' => '梦想学生数',
					'course_time_num' => '梦想课时数',
					'course_num' => '梦想课程数',
					'teacher_coverage' => '梦想教师覆盖率',
					'student_coverage' => '梦想学生覆盖率',
					'class_coverage' => '梦想班级覆盖率',
					'salon' => '所属沙龙' 
			);
		} else if ($_type == 2) {
			$this->top = array (
					'term_name' => '学期',
					'school_number' => '学校代码',
					'school_name' => '学校名称',
					'school_type' => '学校类型',
					'school_nature' => '学校性质',
					'region' => '所属大区',
					'province' => '省/自治区',
					'city' => '市/自治州',
					'area' => '县/区',
					'location' => '学校地址',
					'school_email' => '学校账号',
					'teacher_name' => '教师姓名',
					'teacher_level' => '教师评级',
					'phone' => '电话',
					'email' => '邮箱',
					'sex' => '教师性别',
					'age' => '教师年龄',
					'class_num' => '梦想班级数',
					'class_count' => '梦想班次数',
					'student_num' => '梦想学生数',
					'course_time_num' => '梦想课时数',
					'course_num' => '梦想课程数',
					'blog_num' => '发文数',
					'hot_blog_num' => '精华文章数',
					'valid_integral' => '可兑换积分',
					'sum_integral' => '累计积分' 
			);
		} else if ($_type == 3) {
			$this->top = array (
					'term_name' => '学期',
					'course_name' => '课程名称',
					'school_num' => '学校数',
					'teacher_num' => '梦想教师数',
					'class_num' => '梦想班级数',
					'student_num' => '梦想学生数',
					'class_count' => '梦想班次数',
					'course_time_num' => '梦想课时数' 
			);
		} else if ($_type == 4) {
			$this->top = array (
					'term_name' => '学期',
					'school_number' => '学校代码',
					'school_name' => '学校名称',
					'school_type' => '学校类型',
					'school_nature' => '学校性质',
					'region' => '所属大区',
					'province' => '省/自治区',
					'city' => '市/自治州',
					'area' => '县/区',
					'location' => '学校地址',
					'school_account' => '学校账号',
					'course_name' => '课程名称',
					'teacher_name' => '教师姓名',
					'class_num' => '梦想班级数',
					'class_count' => '梦想班次数',
					'student_num' => '梦想学生数',
					'course_time_num' => '梦想课时数' 
			);
		}
		$excel->exportCsv ( $listData ['data'], $this->top, $_REQUEST ['excelName'] );
	}
}
