<?php

/**
 * 梦想中心后台管理
 * @author zjj
 *
 */
// 加载后台控制器
tsload ( APPS_PATH . '/admin/Lib/Action/AdministratorAction.class.php' );
class DreamCenterAction extends AdministratorAction {
	public $pageTitle = array ();
	/**
	 * 初始化
	 */
	public function _initialize() {
		$this->pageTitle ['index'] = '梦想中心管理';
		$this->pageTitle ['add'] = '编辑梦想中心';
		parent::_initialize ();
	}
	
	/**
	 * 展示列表
	 */
	public function index() {
		$center = M ( 'DreamCenter' );
		$list = $center->getCenterList ( 20 );
		foreach ( $list ['data'] as &$value ) {
			$do = '<a href=\'' . U ( 'admin/DreamCenter/add', array (
					'id' => $value ['id'] 
			) ) . '\'>编辑</a>&nbsp;';
			$do .= '<a href=\'javascript:void(0)\' onclick="admin.selectAdmin(' . $value ['id'] . ',\'1\')">设置管理员</a>&nbsp;';
			$do .= '<a href=\'javascript:void(0)\' onclick="admin.del(' . $value ['id'] . ',\'学校\')">删除</a>&nbsp;';
			$value ['DO'] = $do;
		}
		
		$this->pageKeyList = array (
				'id',
				'school_number',
				'email',
				'name',
				'dream_number',
				'admin_email',
				'province',
				'city',
				'area',
				'sponsors',
				'DO' 
		);
		// 搜索选项的key值
		$this->searchKey = array (
				'school_number',
				'name' 
		);
		$this->pageButton [] = array (
				'title' => '搜索学校',
				'onclick' => "admin.fold('search_form')" 
		);
		$this->pageButton [] = array (
				'title' => '添加学校',
				'onclick' => 'admin.add(\'' . U ( 'admin/DreamCenter/add' ) . '\')' 
		);
		$this->assign ( 'delUrl', U ( 'admin/DreamCenter/del' ) );
		$this->displayList ( $list );
	}
	/**
	 * 新增/修改
	 */
	public function add() {
		$center = M ( 'DreamCenter' );
		$info = '';
		if (! empty ( $_POST )) {
			$res = $center->addCenter ();
			if ($res) {
				$this->success ( '保存成功！');
			} else {
				$this->error ( '保存失败！'.$center->getLastError());
			}
		}
		if (! empty ( $_REQUEST ['id'] )) {
			$detail = $center->getDetail ( intval ( $_REQUEST ['id'] ) );			
			$detail ['applytime'] = date ( 'Y-m-d H:i:s', $detail ['applytime'] );
			$detail ['assetstime'] = date ( 'Y-m-d H:i:s', $detail ['assetstime'] );
			$detail ['accepttime'] = date ( 'Y-m-d H:i:s', $detail ['accepttime'] );
			$detail ['completetime'] = date ( 'Y-m-d H:i:s', $detail ['completetime'] );
		} else {
			$this->pageTitle [ACTION_NAME] = '添加梦想中心';
		}
		// 区域选项数据
		$areaModel = M ( 'Area' );
		$this->assign ( 'provData', $areaModel->getAreaList ( 0 ) );
		if ($detail ['province']) {
			$this->assign ( 'cityData', $areaModel->getAreaList ( $detail ['province'] ) );
			if ($detail ['city']) {
				$this->assign ( 'areaData', $areaModel->getAreaList ( $detail ['city'] ) );
			}
		}
		$this->setSelects ();
		$this->savePostUrl = U ( 'admin/DreamCenter/add' );
		if (! empty ( $_REQUEST ['id'] )) {
			$this->pageKeyList = array (
					'id',
					'email',
					'name',
					'school_number',
					'dream_number',
					'phone',
					'logo',
					'location',
					'sponsors',
					'zip_code',
					'cid0',
					'school_type',
					'educational_type',
					// 'province',
					// 'city',
					// 'area',
					'admin_email',
					'applytime',
					'assetstime',
					'accepttime',
					'completetime',
					'intro' 
			);
		}else{
			$this->pageKeyList = array (
					'id',
					'email',
					'password',
					'repassword',
					'name',
					'school_number',
					'dream_number',
					'phone',
					'logo',
					'location',
					'sponsors',
					'zip_code',
					'cid0',
					'school_type',
					'educational_type',
					// 'province',
					// 'city',
					// 'area',
					'admin_email',
					'applytime',
					'assetstime',
					'accepttime',
					'completetime',
					'intro'
			);
		}
		$this->onsubmit = 'admin.checkSchool(this)';
		$this->notEmpty = array (
				'name',
				'email',
				'password',
				'repassword',
				'school_number',
				'dream_number' 
		// 'type_name',
		// 'type_title'
				);
		$this->displayConfig ( $detail ,'add');
	}
	/**
	 * 删除
	 */
	public function del() {
		$res = M ( 'DreamCenter' )->delCenter ();
		if ($res) {
			$return ['status'] = 1;
			$return ['data'] = '删除成功';
		} else {
			$return ['status'] = 0;
			$return ['data'] = '删除失败';
		}
		echo json_encode ( $return );
		exit ();
	}
	public function checkAdminEmail() {
		$email = trim ( $_POST ['email'] );
		$map ['u.email'] = $email;
		$res = M ( 'User' )->join ( 'as u left join ts_user_verified uv on u.uid=uv.uid' )->field ( 'u.uid,uv.verified,uv.sid' )->where ( $map )->find ();
		if ($res) {
			if ($res ['verified'] == '1') {
				if ($res ['sid'] != $_POST ['id']) {
					$this->ajaxReturn ( null, '指定的管理员不是该校的梦想老师', '0' );
				} else {
					$this->ajaxReturn ( null, null, '1' );
				}
			} else {
				$this->ajaxReturn ( null, '指定的管理员不是实名认证用户', '0' );
			}
		} else {
			$this->ajaxReturn ( null, '指定的管理员账号不存在', '0' );
		}
	}
	/**
	 * select数据
	 */
	private function setSelects() {
		// 学校类型
		$this->opt ['cid0'] = $this->transData ( M ( 'db_school_category' )->field ( 'id,title' )->findAll (), 'id', 'title' );
		$this->opt ['cid0']=array_merge(array('    '),$this->opt ['cid0']);
		// 学校性质
		$this->opt ['school_type'] = $this->transData ( M ( 'db_school_nature' )->field ( 'id,name' )->findAll () );
		$this->opt ['school_type']=array_merge(array('    '),$this->opt ['school_type']);
		// 办学性质
		$this->opt ['educational_type'] = $this->transData ( M ( 'db_educational_nature' )->field ( 'id,name' )->findAll () );
		$this->opt ['educational_type']=array_merge(array('    '),$this->opt ['educational_type']);
	}
	// 行列转换
	private function transData($data = array(), $key = 'id', $value = 'name') {
		$res = array ();
		foreach ( $data as $v ) {
			$res [$v [$key]] = $v [$value];
		}
		return $res;
	}
	public function setAdmin() {
		$sid = intval ( $_POST ['sid'] );
		$uid = intval ( $_POST ['uid'] );
		$res = M ( 'DreamCenter' )->doDreamAdmin ( $sid, $uid );
		echo $res ? '1' : '0';
	}
	
	/**
	 * 选择学校管理员列表
	 */
	public function adminSelect() {
		$map ['verified'] = '1';
		$map ['sid'] = $_REQUEST ['sid'];
		$map ['type']='0';
		$listData = M ( 'UserVerified' )->join ( 'as uv left join ts_user u on uv.uid=u.uid left join ts_user_group_link gl on uv.uid=gl.uid and gl.user_group_id=\'' . C ( 'USER_GROUP_DREAM_ADMIN' ) . '\'' )->field ( 'u.uid,u.email,uv.phone,uv.realname,gl.id as is_admin' )->where ( $map )->findAll ();
		$this->assign ( 'listData', $listData );
		$this->assign ( 'school', $_REQUEST ['school'] );
		$this->assign ( 'sid', $_REQUEST ['sid'] );
		$this->display ();
	}
}
