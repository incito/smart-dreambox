<?php
class UserVerifiedAction extends AdministratorAction {
	/**
	 * 初始化
	 *
	 * @see AdministratorAction::_initialize()
	 */
	public function _initialize() {
		$this->pageTitle ['verifying'] = '用户认证';
		$this->pageTitle ['verified'] = '已认证列表';
		$this->pageTitle ['addVerifiedUser'] = '添加认证';
		$this->pageTitle ['setVerifyRuler'] = '设置认证规则';
		// $this->pageTitle ['synchroAllVerified'] = '一键同步认证';
		parent::_initialize ();
	}
	
	/**
	 * 需验证用户列表
	 */
	public function verifying() {
		$_GET ['page'] = 'verifying';
		$this->verified ();
	}
	/**
	 * 已验证用户列表
	 */
	public function verified() {
		$verified = ('verifying' == $_GET ['page']) ? '0' : '1';
		$this->_initUserListAdminMenu ( '0' == $verified ? 'verifying' : 'verifyed' );
		// 为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
		if (! empty ( $_POST )) {
			$_SESSION ['admin_searchVerifiedUser'] = serialize ( $_POST );
			$this->assign ( 'type', 'searchUser' );
		} else if (isset ( $_GET [C ( 'VAR_PAGE' )] ) && ! is_null ( $_SESSION ['admin_searchVerifiedUser'] )) {
			$_POST = unserialize ( $_SESSION ['admin_searchVerifiedUser'] );
			$this->assign ( 'type', 'searchUser' );
		} else {
			unset ( $_SESSION ['admin_searchVerifiedUser'] );
		}
		
		$_POST ['uid'] && $map ['uv.uid'] = t ( $_POST ['uid'] );
		$_POST ['realname'] && $map ['uv.realname'] = array (
				'LIKE',
				'%' . t ( $_POST ['realname'] ) . '%' 
		);
		$_POST ['phone'] && $map ['uv.phone'] = array (
				'LIKE',
				'%' . t ( $_POST ['phone'] ) . '%' 
		);
		$_POST ['uname'] && $map ['u.uname'] = array (
				'LIKE',
				'%' . t ( $_POST ['uname'] ) . '%' 
		);
		$_POST ['email'] && $map ['u.email'] = array (
				'LIKE',
				'%' . t ( $_POST ['email'] ) . '%' 
		);
		$map ['verified'] = "{$verified}";
		
		$dataList = M ( 'UserVerified' )->join ( "as uv left join ts_user u on uv.uid=u.uid left join ts_user_group_link gl on u.uid=gl.uid and gl.user_group_id='" . C ( 'USER_GROUP_DREAM_ADMIN' ) . "'" )->where ( $map )->field ( "uv.*,u.uname,u.email,gl.id as is_admin" )->order('vtime desc')->findPage ();
		
		$data = $dataList ['data'];
		foreach ( $data as $v ) {
			$attach_id = $v ['attachment'];
			$attach = D ( 'attach' )->where ( 'attach_id =' . $attach_id )->find ();
			$v ['attachment'] = $attach ['name'];
			$v ['attach_id'] = $attach ['attach_id'];
			$data1 [] = $v;
		}
		$this->assign ( 'data1', $data1 );
		$this->assign ( $_POST );
		$this->assign ( 'verified', $verified );
		$this->assign ( $dataList );
		$this->display ( 'verified' );
	}
	
	/**
	 * 审批界面
	 */
	public function doVerifiedTab() {
		if (intval ( $_GET ['uid'] ) > 0) {
			$verified = M ( 'UserVerified' )->join ( 'left join ts_db_school s on sid=s.id' )->field ( 'ts_user_verified.uid,ts_user_verified.idcard,ts_user_verified.reason,ts_user_verified.phone,ts_user_verified.attachment,ts_user_verified.realname,s.name as school' )->where ( "ts_user_verified.uid={$_GET['uid']}" )->find ();
			$verified ['attachment'] = $this->getAttachUrl ( $verified ['attachment'] );
			$this->assign ( 'data', $verified );
		}
		$this->_removeVerifiedCache ( $_GET ['uid'] );
		$this->display ( 'doVerifiedTab' );
	}
	/**
	 * 审批通过
	 */
	public function doVerified() {
		// // 一键同步认证 TODO
		// if ($_REQUEST ['synchro'] == 1) {
		// if ($uid = intval ( $_REQUEST ['uid'] )) {
		// $sql_where = " and u.uid >= $uid ";
		// }
		// $model = M ( "UserVerified" );
		// $sql = "select v.uid from ts_user_verified v left join ts_user u on v.uid=u.uid left join ts_db_school_member m on u.uid=m.uid left join ts_db_school g on m.sid=g.id where v.verified='1' and m.level='0'" . $sql_where . " order by u.uid asc limit 0,100";
		
		// $verified_list = M ()->query ( $sql );
		// var_dump ( $verified_list );
		// die ();
		// foreach ( $verified_list as $val ) {
		// $uid [] = $val ['uid'];
		// }
		// } else {
		$uid = is_array ( $_POST ['uid'] ) ? t ( $_POST ['uid'] ) : explode ( ',', t ( $_POST ['uid'] ) ); // 判读是不是数组
		$uid=array_unique($uid);                                                                                             // }
		$data ['verified'] = '1';
		$data ['vtime'] = time ();
		$map ['uid'] = array (
				'IN',
				$uid 
		);
		$map['verified']=array('neq','1');
		$userVerifiedModel = M ( 'user_verified' );
		$result = $userVerifiedModel->where ( $map )->save ( $data ); // 通过认证
		$result = $result && model ( 'UserGroupLink' )->moveGroup ( $uid, C ( 'USER_GROUP_TEACHER' ) ); // 移动用户组;
		                                                                                                // 通过认证后插入积分初始信息
		$notifyModel=model('Notify');
		$integralModel = M ( 'Integral' );

		// 清除权限缓存
		$cacheModel=model ( 'Cache' );
		if ($result) {
			foreach ( $uid as $id ) {
				//发送通过审核通知
				$notifyModel->sendNotify($id, 'admin_user_doverify_ok');
				$find = $integralModel->where ( 'ref_id=' . $id )->find ();
				// TODO 通过认证后是否解冻原帐户积分？
				if ($find) {
				} else {
					$result = $result && $integralModel->add ( array (
							'ref_id' => $id,
							'ref_type' => '0' 
					) );
				}
				$cacheModel->rm ( 'perm_user_' . $id );
				$cacheModel->rm ( 'user_group_' . $id );
			}		
			model ( 'User' )->cleanCache ( $uid );
			echo 1;
		} else {
			echo 0;
		}
	}
	/**
	 * 驳回/取消
	 */
	public function deleteVerified() {
		// $reason = trim ( $_POST ['reason'] );
		// $admin_reject = "认证被驳回";
		// $reject_data = array('title' => $admin_reject, 'content' => $reason);
		// if(is_array($_POST ['uid'])){
		// foreach($_POST ['uid'] as $uid_temp){
		// $res_temp = service('Notify')->sendIn($uid_temp, 'admin_notification', $reject_data);
		// }
		// }else{
		// $res_temp = service('Notify')->sendIn($_POST['uid'], 'admin_notification', $reject_data);
		// }
		$uid = is_array ( $_POST ['uid'] ) ? implode ( ',', $_POST ['uid'] ) : $_POST ['uid']; // 判读是不是数组
		$uidarr = explode ( ',', $uid );
		$uidarr=array_unique($uidarr);
		// 删除认证信息
		$user_verified_model = M ( 'user_verified' );
		$res = $user_verified_model->where ( 'uid IN (' . t ( $uid ) . ') and verified!=\'2\'' )->save ( array (
				'verified' => '2' 
		) );
		if ($res) {
			// 移出梦想老师用户组
			model ( 'UserGroupLink' )->moveGroup ( $uid, null, C ( 'USER_GROUP_TEACHER' ) );
			// 移出学校管理员用户组
			model ( 'UserGroupLink' )->moveGroup ( $uid, null, C ( 'USER_GROUP_DREAM_ADMIN' ) );
			$notifyModel=model('Notify');
			$config['reason']=t($_POST['reason']);
			foreach ($uidarr as $id){			
				$notifyModel->sendNotify($id,'admin_user_doverify_reject',$config);
			}
			$this->_removeVerifiedCache ( $uidarr );
			if (strpos ( $_POST ['uid'], ',' ) !== FALSE) {
				echo 1;
			} else {
				echo 2;
			}
			
			model ( 'User' )->cleanCache ( $uidarr );
			$cacheModel=model ( 'Cache' );
			// 清除权限缓存
			foreach ( $uidarr as $value ) {
				$cacheModel->rm ( 'perm_user_' . $value );
				$cacheModel->rm ( 'user_group_' . $value );
			}
			
			// 发送通知
			// $uids = is_array ( $_POST ['uid'] ) ? $_POST ['uid'] : explode ( ',', $_POST ['uid'] );
			// $notify_dao = service ( 'Notify' );
			// $notify_tpl = (1 == $_POST ['verified']) ? 'admin_delverified' : 'admin_rejectverified';
			// foreach ( $uids as $v ) {
			// S ( 'S_userInfo_' . $v, null );
			// $notify_dao->sendIn ( $v, $notify_tpl, array (
			// 'reason' => t ( urldecode ( "实名认证用户" ) )
			// ) ); // $_POST['reason']
			// }
		} else {
			echo 0;
		}
	}
	
	/**
	 * 驳回页面
	 */
	public function deleteVerifiedTab() {
		$this->display ( 'deleteVerifiedTab' );
	}
	
	/**
	 * 添加认证
	 */
	public function addVerifiedUser() {
		$this->_initUserListAdminMenu ( 'addVerifiedUser' );
		$this->assign ( 'tabHash', 'addVerifiedUser' );
		if (intval ( $_GET ['uid'] ) > 0) {
			$verified = M ( 'user_verified' )->where ( 'uid=' . intval ( $_GET ['uid'] ) )->find ();
			$verified ['uid'] = intval ( $_GET ['uid'] );
			$this->assign ( 'verified', $verified );
			// $this->assign ( 'jumpUrl', $_SERVER ['HTTP_REFERER'] );
		}
		
		$this->display ( 'addVerifiedUser' );
	}
	/**
	 * 保存添加认证
	 */
	public function saveVerified() {
		$data = M ( 'user_verified' )->create ();
		if (! $data ['uid']) {
			$this->error ( '请选择用户' );
		}
		// if (!$data['info']) {
		// $this->error('请填写认证原因');
		// }
		
		$uid = t ( $_POST ['uid'] );
		$res_user = M ( 'user' )->where ( 'uid =' . $_POST ['uid'] )->find ();
		if (! $res_user) {
			$this->error ( '该用户不存在！' );
		}
		
		// 移动用户组
		if ($_POST ['verified'] == '1') {
			model ( 'UserGroupLink' )->moveGroup ( $uid, C ( 'USER_GROUP_TEACHER' ) ); // 梦想老师用户组
		} else {
			model ( 'UserGroupLink' )->moveGroup ( $uid, null, C ( 'USER_GROUP_TEACHER' ) ); // 普通用户组
		}
		$res_search = M ( 'user_verified' )->where ( 'uid =' . $_POST ['uid'] )->find ();
		if (! $res_search) {
			$data ['realname'] = $res_user ['uname'];
			$result = M ( 'user_verified' )->add ( $data );
			if ($result) {
				$jumpUrl = U ( 'admin/UserVerified/addVerifiedUser', array (
						'uid' => $_POST ['uid'],
						'tabHash' => 'addVerifiedUser' 
				) );
				$this->assign ( 'jumpUrl', $jumpUrl );
				$this->success ();
			}
		} else {
			$map ['uid'] = t ( $_POST ['uid'] );
			$res = M ( 'user_verified' )->where ( $map )->save ( $data );
			$this->_removeVerifiedCache ( $uid );
			
			if (false !== $res) {
				S ( 'S_userInfo_' . $uid, null );
				$this->_removeVerifiedCache ( $data ['uid'] );
				// ttp://localhost/ts/index.php?app=admin&mod=Addons&act=admin&pluginid=7&page=addVerifiedUser
				// $jumpUrl = $_POST['jumpUrl'] ? $_POST['jumpUrl'] : U('admin/User/addVerifiedUser');
				$jumpUrl = U ( 'admin/UserVerified/addVerifiedUser', array (
						'uid' => $_POST ['uid'],
						'tabHash' => 'addVerifiedUser' 
				) );
				$this->assign ( 'jumpUrl', $jumpUrl );
				$this->success ();
			} else {
				$this->error ();
			}
		}
	}
	
	/**
	 * 将该用户设置成梦想中心管理员
	 */
	public function doDreamAdmin() {
		$id = $_POST ['id'];
		$vm = M ( 'UserVerified' );
		$groupLink = M ( 'UserGroupLink' );
		if (! is_array ( $id )) {
			$id = explode ( ',', $id );
			$dreamM = M ( 'DreamCenter' );
			foreach ( $id as $v ) {
				$sid = $vm->where ( 'uid=\'' . $v . '\'' )->getField ( 'sid' );
				$dreamM->doDreamAdmin ( $sid, $v );
			}
		}
		$this->ajaxReturn ( null, null, 1 );
	}
	/**
	 * 设置认证规则
	 */
	public function setVerifyRuler() {
		// $data = model('AddonData')->get('verifyruler');
		$data = model ( 'Xdata' )->lget ( 'square', $_POST );
		$this->_initUserListAdminMenu ( 'setVerifyRuler' );
		$this->assign ( $data );
		$this->display ();
	}
	public function saveVerifyRuler() {
		model ( 'Xdata' )->lput ( 'square', $_POST );
		$this->assign ( 'jumpUrl', U ( 'admin/UserVerified/setVerifyRuler', array (
				'tabHash' => 'setVerifyRuler' 
		) ) );
		$this->success ( '保存成功！' );
	}
	
	// 同步所有认证
	public function synchroAllVerified() {
		$this->_initUserListAdminMenu ( 'synchroAllVerified' );
		$this->display ( 'synchroAllVerified' );
	}
	/**
	 * 初始化用户认证菜单
	 *
	 * @param string $type
	 *        	列表类型，index、pending、dellist
	 */
	private function _initUserListAdminMenu($type) {
		// tab选项
		$this->pageTab [] = array (
				'title' => '待认证',
				'tabHash' => 'verifying',
				'url' => U ( 'admin/UserVerified/verifying' ) 
		);
		$this->pageTab [] = array (
				'title' => '已认证',
				'tabHash' => 'verified',
				'url' => U ( 'admin/UserVerified/verified' ) 
		);
		$this->pageTab [] = array (
				'title' => '添加认证',
				'tabHash' => 'addVerifiedUser',
				'url' => U ( 'admin/UserVerified/addVerifiedUser' ) 
		);
		// $this->pageTab[] = array('title'=>'在线用户列表','tabHash'=>'online','url'=>U('admin/User/online'));
		$this->pageTab [] = array (
				'title' => '设置认证规则',
				'tabHash' => 'setVerifyRuler',
				'url' => U ( 'admin/UserVerified/setVerifyRuler' ) 
		);
		// $this->pageTab [] = array (
		// 'title' => '一键同步认证',
		// 'tabHash' => 'synchroAllVerified',
		// 'url' => U ( 'admin/UserVerified/synchroAllVerified' )
		// );
		$this->assign ( 'tabHash', $type );
	}
	// 附件地址
	private function getAttachUrl($att_id) {
		$Attach = new Model ();
		$sql = "select name,save_path,save_name from ts_attach where attach_id=" . $att_id;
		$data = $Attach->query ( $sql );
		return $data [0];
	}
	private function _removeVerifiedCache($uid) {
		if (is_array ( $uid )) {
			foreach ( $uid as $id ) {
				S ( 'verified_' . $uid, null );
			}
		} else {
			S ( 'verified_' . $uid, null );
		}
	}
	// private function saveSchoolMember($is_exists, $data) {
	// if (! $is_exists) {
	// M ( 'SchoolMember', 'public' )->add ( array (
	// 'level' => $_POST ['verified'] == '0' ? '0' : 1,
	// 'name' => $data ['realname'],
	// 'ctime' => time (),
	// 'mtime' => time (),
	// 'sid' => $_POST ['sid'],
	// 'uid' => $data ['uid']
	// ) );
	// } else {
	// M ( 'SchoolMember', 'public' )->where ( 'uid =' . $_POST ['uid'] )->save ( array (
	// 'sid' => $_POST ['sid']
	// ) );
	// }
	// }
}