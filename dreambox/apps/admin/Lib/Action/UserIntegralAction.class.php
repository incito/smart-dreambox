<?php
/**
 * 用户/学校积分管理
 */
class UserIntegralAction extends AdministratorAction {
	public $pageTitle = array ();
	/**
	 * 初始化
	 *
	 * @see AdministratorAction::_initialize()
	 */
	public function _initialize() {
		$this->pageTitle ['teacherIntegrals'] = '教师管理';
		$this->pageTitle ['schoolIntegrals'] = '学校管理';
		$this->pageTitle ['teacherIntegralHistorys'] = '教师明细';
		$this->pageTitle ['schoolIntegralHistorys'] = '学校明细';
		$this->pageTitle ['toImport'] = '积分导入';
		parent::_initialize ();
	}
	/**
	 * 教师积分查询
	 */
	public function teacherIntegrals() {
		$integralModel = M ( 'Integral' );
		$listData = $integralModel->getUserIntegralList ( 20 );
		if (! $listData) {
			$this->error ( $integralModel->error );
		}
		
		$this->_initPage ();
		$this->pageKeyList = array (
				'realname',
				'school',
				'score',
				'sum_integral',
				'email',
				'phone',
				'is_frozen' 
		);
		
		// 搜索选项的key值
		$this->searchKey = array (
				'realname',
				'email',
				'phone',
				'school',
				'is_deleted',
				'is_frozen' 
		);
		$this->pageButton [] = array (
				'title' => '搜索',
				'onclick' => "admin.fold('search_form')" 
		);
		$this->pageButton [] = array (
				'title' => '导出excel',
				'onclick' => 'admin.exportExcel(\'' . U ( 'admin/UserIntegral/exportTeacherIntegrals' ) . '\')' 
		);
		$this->displayList ( $listData );
	}
	/**
	 * 学校积分查询
	 */
	public function schoolIntegrals() {
		$integralModel = M ( 'Integral' );
		$listData = $integralModel->getSchoolIntegralList ( 20 );
		// 无权限跳转到错误提示页面
		if (! $listData) {
			$this->error ( $integralModel->error );
		}
		$this->_initPage ( 'school' );
		$this->pageKeyList = array (
				'school_number',
				'school',
				'score',
				'sum_integral',
				'phone',
				'is_frozen' 
		);
		// 搜索选项的key值
		$this->searchKey = array (
				'school_number',
				'school',
				'email',
				'is_deleted',
				'is_frozen' 
		);
		$this->pageButton [] = array (
				'title' => '搜索',
				'onclick' => "admin.fold('search_form')" 
		);
		$this->pageButton [] = array (
				'title' => '导出excel',
				'onclick' => 'admin.exportExcel(\'' . U ( 'admin/UserIntegral/exportSchoolIntegrals' ) . '\')' 
		);
		$this->displayList ( $listData );
	}
	
	/**
	 * 教师积分详细
	 */
	public function teacherIntegralHistorys() {
		$integralModel = M ( 'Integral' );
		$listData = $integralModel->getTeachersIntegralHistory ( 20 );
		// 无权限跳转到错误提示页面
		if (! $listData) {
			$this->error ( $integralModel->error );
		}
		$this->_initPage ( 'teacherHistory' );
		
		$this->pageKeyList = array (
				'name',
				'email',
				'old_integral',
				'increase_integral',
				'new_integral',
				'type',
				'comment',
				'operator',
				'ctime' 
		);
		// 搜索选项的key值
		$this->searchKey = array (
				'name',
				'email',
				'integral_type',
				array (
						'ctime',
						'ctime1' 
				) 
		);
		$this->pageButton [] = array (
				'title' => '搜索',
				'onclick' => "admin.fold('search_form')" 
		);
		$this->pageButton [] = array (
				'title' => '导出excel',
				'onclick' => 'admin.exportExcel(\'' . U ( 'admin/UserIntegral/exportTeacherHistory' ) . '\')' 
		);
		
		$this->displayList ( $listData );
	}
	
	/**
	 * 学校积分详细
	 */
	public function schoolIntegralHistorys() {
		$integralModel = M ( 'Integral' );
		$listData = $integralModel->getSchoolIntegralHistory ( 20 );
		// 无权限跳转到错误提示页面
		if (! $listData) {
			$this->error ( $integralModel->error );
		}
		$this->_initPage ( 'schoolHistory' );
		
		$this->pageKeyList = array (
				'school',
				'email',
				'old_integral',
				'increase_integral',
				'new_integral',
				'type',
				'comment',
				'operator',
				'ctime' 
		);
		// 搜索选项的key值
		$this->searchKey = array (
				'school',
				'school_number',
				'integral_type',
				array (
						'ctime',
						'ctime1' 
				) 
		);
		$this->pageButton [] = array (
				'title' => '搜索',
				'onclick' => "admin.fold('search_form')" 
		);
		$this->pageButton [] = array (
				'title' => '导出excel',
				'onclick' => 'admin.exportExcel(\'' . U ( 'admin/UserIntegral/exportSchoolHistory' ) . '\')' 
		);
		$this->displayList ( $listData );
	}
	/**
	 * 导出教师积分
	 */
	public function exportTeacherIntegrals() {
		$integralModel = M ( 'Integral' );
		$listData = $integralModel->query ( "SELECT s. NAME AS school, u.email, u1.email AS school_email, uv.phone, uv.realname, uv.`level`, uv.idcard, uv.bankaccount, uv.bankname, a1.title AS prov, a2.title AS city, a3.title AS area, ( CASE WHEN ha3.area IS NOT NULL THEN ha3.area WHEN ha2.area IS NOT NULL THEN ha2.area WHEN ha1.area IS NOT NULL THEN ha1.area END ) AS big_area, dsc.title AS school_category, dsn.`name` AS school_nature, en.`name` AS educational_nature, ( ts_db_integral.self_integral + ts_db_integral.trans_integral ) AS integral, ts_db_integral.sum_integral FROM `ts_db_integral` INNER JOIN ts_user_verified uv ON ts_db_integral.ref_id = uv.uid AND uv.type = '0' INNER JOIN ts_db_school s ON uv.sid = s.id AND uv.verified = '1' INNER JOIN ts_user u ON uv.uid = u.uid LEFT JOIN ts_area a1 ON s.province = a1.area_id LEFT JOIN ts_area a2 ON s.city = a2.area_id LEFT JOIN ts_area a3 ON s.area = a3.area_id LEFT JOIN hz_area ha3 ON s.area = ha3.`code` LEFT JOIN hz_area ha2 ON s.city = ha2.`code` LEFT JOIN hz_area ha1 ON s.province = ha1.`code` LEFT JOIN ts_db_school_category dsc ON dsc.id = s.cid0 LEFT JOIN ts_db_school_nature dsn ON dsn.id = s.school_type LEFT JOIN ts_db_educational_nature en ON s.educational_type = en.id LEFT JOIN ts_user_verified uv1 ON uv.sid = uv1.sid AND uv1.type = 1 AND uv1.verified = '1' LEFT JOIN ts_user u1 ON uv1.uid = u1.uid WHERE (`is_deleted` = '0') AND (`is_frozen` = '0') ORDER BY uv.id ASC" );
		// 无权限跳转到错误提示页面
		if (! $listData) {
			$this->error ( $integralModel->error );
		}
		// 载入Excel操作类
		require_once ADDON_PATH . '/library/Excel.class.php';
		$excel = new Excel ();
		$top = array (
				'realname' => '教师姓名',
				'big_area' => '所属大区',
				'prov' => '省/自治区',
				'city' => '市/自治州',
				'area' => '县/区',
				'school' => '学校名称',
				'school_category' => '学校类型',
				'school_nature' => '学校性质',
				'educational_nature' => '办学性质',
				'school_email' => '学校账号',
				'level' => '教师评级',
				'phone' => '教师电话',
				'email' => '教师邮箱',
				'idcard' => '身份证号码',
				'bankaccount' => '银行账号',
				'bankname' => '开户行信息',
				'integral' => '可兑换积分',
				'sum_integral' => '累计积分' 
		);
		$excel->exportCsv ( $listData, $top, '教师积分' );
	}
	/**
	 * 导出学校积分
	 */
	public function exportSchoolIntegrals() {
		$integralModel = M ( 'Integral' );
		$listData = $integralModel->getSchoolIntegralList ( 9999999999 );
		// 无权限跳转到错误提示页面
		if (! $listData) {
			$this->error ( $integralModel->error );
		}
		// 载入Excel操作类
		require_once ADDON_PATH . '/library/Excel.class.php';
		$excel = new Excel ();
		$top = array (
				'email' => '邮箱',
				'score' => '积分' 
		);
		$excel->export ( $listData ['data'], $top, '学校积分' );
	}
	/**
	 * 导出教师积分明细
	 */
	public function exportTeacherHistory() {
		$integralModel = M ( 'Integral' );
		$listData = $integralModel->getTeachersIntegralHistory ( 9999999999 );
		// 无权限跳转到错误提示页面
		if (! $listData) {
			$this->error ( $integralModel->error );
		}
		// 载入Excel操作类
		require_once ADDON_PATH . '/library/Excel.class.php';
		$excel = new Excel ();
		$top = array (
				'name' => '姓名',
				'email' => '邮箱',
				'old_integral' => '原有积分',
				'increase_integral' => '变动积分',
				'new_integral' => '现有积分',
				'type' => '变动类型',
				'comment' => '变动原因',
				'operator' => '操作者',
				'ctime' => '操作时间' 
		);
		$excel->export ( $listData ['data'], $top, '教师积分明细' );
	}
	/**
	 * 导出学校积分明细
	 */
	public function exportSchoolHistory() {
		$integralModel = M ( 'Integral' );
		$listData = $integralModel->getSchoolIntegralHistory ( 9999999999 );
		// 无权限跳转到错误提示页面
		if (! $listData) {
			$this->error ( $integralModel->error );
		}
		// 载入Excel操作类
		require_once ADDON_PATH . '/library/Excel.class.php';
		$excel = new Excel ();
		$top = array (
				'school' => '学校',
				'email' => '邮箱',
				'old_integral' => '原有积分',
				'increase_integral' => '变动积分',
				'new_integral' => '现有积分',
				'type' => '变动类型',
				'comment' => '变动原因',
				'operator' => '操作者',
				'ctime' => '操作时间' 
		);
		$excel->export ( $listData ['data'], $top, '学校积分明细' );
	}
	
	/**
	 * 积分导入页面
	 */
	public function toImport() {
		$this->_initPage ( 'toImport' );
		$this->assign ( 'pageTab', $this->pageTab );
		$this->assign ( 'pageTitle', '积分导入' );
		$this->display ( 'import' );
	}
	/**
	 * 导入积分日志
	 */
	public function doImport() {
		$integralModel = M ( 'Integral' );
		$ret = $integralModel->importHistory ( $_FILES ['excel'] );
		if ($ret === true) {
			$this->success ( '导入成功' );
		} else {
			$this->assign ( 'msg', $integralModel->error );
			$this->display ( 'fail' );
		}
	}
	public function downloadTemplate() {
		$file_path = DATA_PATH . '/excel/template/score_template.xls';
		if (file_exists ( $file_path )) {
			Http::download ( $file_path, iconv ( 'UTF-8', 'GB2312', '积分导入模板.xls' ) );
		} else {
			$this->error ( L ( 'attach_noexist' ) );
		}
	}
	
	/**
	 * 页面初始化
	 */
	private function _initPage($type = 'teacher') {
		// tab选项
		$this->pageTab [] = array (
				'title' => '教师管理',
				'tabHash' => 'teacher',
				'url' => U ( 'admin/UserIntegral/teacherIntegrals' ) 
		);
		$this->pageTab [] = array (
				'title' => '学校管理',
				'tabHash' => 'school',
				'url' => U ( 'admin/UserIntegral/schoolIntegrals' ) 
		);
		$this->pageTab [] = array (
				'title' => '教师明细',
				'tabHash' => 'teacherHistory',
				'url' => U ( 'admin/UserIntegral/teacherIntegralHistorys' ) 
		);
		$this->pageTab [] = array (
				'title' => '学校明细',
				'tabHash' => 'schoolHistory',
				'url' => U ( 'admin/UserIntegral/schoolIntegralHistorys' ) 
		);
		$this->pageTab [] = array (
				'title' => '积分导入',
				'tabHash' => 'toImport',
				'url' => U ( 'admin/UserIntegral/toImport' ) 
		);
		$this->opt ['is_deleted'] = array (
				'0' => '启用',
				'1' => '未启用' 
		);
		$this->opt ['is_frozen'] = array (
				'-1' => '请选择',
				'0' => '未冻结',
				'1' => '已冻结' 
		);
		$this->opt ['integral_type'] = array (
				'' => '请选择',
				'1' => '转入',
				'2' => '转出' 
		);
		$_REQUEST ['tabHash'] = $type;
	}
}