<?php
/**
 * 梦想中心管理
 * @author admin
 *
 */
class DreamCenterModel extends Model {
	protected $tableName = 'db_school';
	protected  $error='';
	/**
	 * 查询梦想中心
	 *
	 * @param number $limit        	
	 * @return unknown
	 */
	public function getCenterList($limit = 20) {
		if (! empty ( $_POST )) {
			$_POST ['name'] && $map ['s.name'] = array (
					'LIKE',
					'%' . t ( $_POST ['name']) . '%' 
			);
			$_POST ['school_number'] && $map ['s.school_number'] = array (
					'LIKE',
					'%' . t ( $_POST ['school_number']) . '%'  
			);
		}
		$list = $this->where ( $map )->join ( 'as s left join ts_area a on s.province=a.area_id left join ts_area a1 on s.city=a1.area_id left join ts_area a2 on s.area=a2.area_id' )->field ( 's.id,s.school_number,s.email,s.name,a.title as province,a1.title as city,a2.title as area,s.dream_number,s.sponsors, (select u.email from ts_user_verified uv left join ts_user_group_link gl on uv.uid=gl.uid and gl.user_group_id=\'' . C ( 'USER_GROUP_DREAM_ADMIN' ) . '\' inner join ts_user u on gl.uid=u.uid where  uv.verified=\'1\' and uv.sid=s.id limit 1) as admin_email' )->findPage ( $limit );
		return $list;
	}
	/**
	 * 新增/修改
	 *
	 * @see Model::add()
	 */
	public function addCenter() {
		$_POST ['id'] && $data ['id'] = t ( $_POST ['id'] );
		$_POST ['name'] && $data ['name'] = t ( $_POST ['name'] ); // 学校名
		$data ['name'] && $data ['first_letter'] = getFirstLetter($data ['name']); //首字母
		$_POST ['school_number'] && $data ['school_number'] = t ( $_POST ['school_number'] ); // 学校名
		$_POST ['location'] && $data ['location'] = t ( $_POST ['location'] ); // 学校地址
		$_POST ['sponsors'] && $data ['sponsors'] = t ( $_POST ['sponsors'] ); // 冠名方
		$_POST ['email'] && $data ['email'] = t ( $_POST ['email'] ); // 学校邮箱
		$_POST ['phone'] && $data ['phone'] = t ( $_POST ['phone'] ); // 学校电话
		$_POST ['zip_code'] && $data ['zip_code'] = t ( $_POST ['zip_code'] ); // 学校邮编
		$_POST ['province'] && $data ['province'] = t ( $_POST ['province'] ); // 省
		$_POST ['city'] && $data ['city'] = t ( $_POST ['city'] ); // 市
		$_POST ['area'] && $data ['area'] = t ( $_POST ['area'] ); // 区县县
		$_POST ['admin_email'] && $data ['admin_email'] = t ( $_POST ['admin_email'] ); // 管理员邮箱
		$_POST ['applytime'] && $data ['applytime'] = strtotime ( t ( $_POST ['applytime'] ) ); // 申请时间
		$_POST ['assetstime'] && $data ['assetstime'] = strtotime ( t ( $_POST ['assetstime'] ) ); // 评估通过时间
		$_POST ['accepttime'] && $data ['accepttime'] = strtotime ( t ( $_POST ['accepttime'] ) ); // 收货时间
		$_POST ['completetime'] && $data ['completetime'] = strtotime ( t ( $_POST ['completetime'] ) ); // 竣工时间
		$_POST ['dream_number'] && $data ['dream_number'] = t ( $_POST ['dream_number'] ); // 梦想中心序号
		$_POST ['intro'] && $data ['intro'] = t ( $_POST ['intro'] ); // 学校介绍
		$_POST ['logo'] && $data ['logo'] = intval ( $_POST ['logo'] ); // 学校logo
		$data ['cid0'] = intval ( $_POST ['cid0'] ); // 学校类型
		$data ['school_type'] = intval ( $_POST ['school_type'] ); // 学校性质
		$data ['educational_type'] = intval ( $_POST ['educational_type'] ); // 办学性质
		if ($data ['id']) {
			// 如果管理员保存失败，返回
			if (! $this->changeAdmin ( $data ['id'], $data ['admin_email'] )) {
				return false;
			}
			$map ['id'] = $data ['id'];
			$res = $this->where ()->save ( $data );
		} else {
			$register_model=M('Register');
			if(!$register_model->isValidPassword($_POST ['password'], $_POST ['repassword'])){
				$register_model->getLastError()&&$this->error='学校账号创建失败:'.$register_model->getLastError();
				return false;
			}
			$this->startTrans();
			try{
    			$res = $this->add ( $data );
    			//创建账号
    			$user_model=M('User');
    			$res=$user_model->createSchoolAccount($res,$data ['name'],$data ['email'],$_POST ['password']);
    			if(!$res){
    				$user_model->getLastError()&&$this->error='学校账号创建失败:'.$user_model->getLastError();
    				$this->rollback();
    			}else{
    				$this->commit();				
    			}
    			return $res;
			}
			//捕获异常
			catch(Exception $e)
			{
			    $this->rollback ();
			    return false;
			}
		}
		if ($res && $data ['admin_email']) {
			$adminUid = M ( 'User' )->where ( array (
					'email' => $data ['admin_email'] 
			) )->getField ( 'uid' );
			return M ( 'UserGroupLink' )->moveGroup ( $adminUid, C ( 'USER_GROUP_DREAM_ADMIN' ) );
		}
		return $res;
	}
	
	/**
	 * detail
	 */
	public function getDetail($id) {
		return $this->field ( 'ts_db_school.*,(select u.email from ts_user u left join ts_user_verified uv on u.uid=uv.uid left join ts_user_group_link gl on u.uid=gl.uid  where uv.sid=ts_db_school.id and gl.user_group_id=\'' . C ( 'USER_GROUP_DREAM_ADMIN' ) . '\' and uv.verified=\'1\' limit 1)as admin_email' )->where ( 'id=\'' . $id . '\'' )->find ();
	}
	/**
	 * 删除
	 */
	public function delCenter() {
		$ids = $_POST ['id'];
		if (is_array ( $ids )) {
			$ids = implode ( ',', $ids );
		}
		$map ['id'] = array (
				'IN',
				$ids 
		);
		$ret=$this->where ( $map )->delete ();
		if($ret){
			unset($map['id']);
			$map['sid']=array (
				'IN',
				$ids 
			);
			//删除学校账号
			M('User')->deleteSchoolAccount($ids);
			$UVModel=M('UserVerified');
			//获得学校下所有的教师
			$uids=$UVModel->where($map)->field('uid')->select();			
			$uids=getSubByKey($uids,'uid');
			//将学校下的教师移除该学校并驳回实名认证
			$data['verified']='2';
			$data['sid']='0';
			$UVModel->where($map)->save($data);
			unset($map['sid']);
			//学校下所有教师移除梦想教师用户组
			M('UserGroupLink')->moveGroup($uids,null,C('USER_GROUP_TEACHER'));
			//学校下所有教师移除学校管理员用户组
			M('UserGroupLink')->moveGroup($uids,null,C('USER_GROUP_DREAM_ADMIN'));
		}
		return $ret;
	}
	/**
	 * 更换梦想中心管理员
	 *
	 * @param unknown $sid        	
	 * @param unknown $email        	
	 * @return boolean
	 */
	public function changeAdmin($sid, $email) {
		// 获得原梦想中心管理员ID
		$old = M ( 'UserVerified' )->join ( 'as uv left join ts_user_group_link gl on uv.uid=gl.uid left join ts_user u on uv.uid=u.uid ' )->where ( array (
				'uv.sid' => $sid,
				'gl.user_group_id' => C ( 'USER_GROUP_DREAM_ADMIN' ),
				'uv.verified' => '1' 
		) )->field ( 'uv.uid,u.email' )->find ();
		// 如果没有改变管理员，直接返回
		if ($email == $old ['email']) {
			return true;
		}
		$groupLink = M ( 'UserGroupLink' );
		$groupLink->moveGroup ( $old ['uid'], null, C ( 'USER_GROUP_DREAM_ADMIN' ) );
		
		// email为空直接返回
		if (empty ( $email )) {
			return true;
		}
		$newU = M ( 'User' )->join ( 'as u left join ts_user_verified uv on u.uid=uv.uid ' )->where ( array (
				'u.email' => $email,
				'uv.sid' => $sid,
				'uv.verified' => '1' 
		) )->getField ( 'uv.uid' );
		if ($newU) {
			$groupLink->moveGroup ( $newU, C ( 'USER_GROUP_DREAM_ADMIN' ) );
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * 给指定梦想中心设置管理员
	 */
	public function doDreamAdmin($sid, $uid) {
		if (empty ( $sid ) || empty ( $uid )) {
			return false;
		}
		$id = $_POST ['id'];
		$vm = M ( 'UserVerified' );
		$groupLink = M ( 'UserGroupLink' );
		$verified = $vm->where ( 'uid=\'' . $uid . '\' and sid=\'' . $sid . '\'' )->getField ( 'verified' );
		// 已认证用户且属于该学校
		if ($verified == '1') {
			// 获得原梦想中心管理员ID
			$old = M ( 'UserVerified' )->join ( 'as uv left join ts_user_group_link gl on uv.uid=gl.uid ' )->where ( array (
					'uv.sid' => $sid,
					'gl.user_group_id' => C ( 'USER_GROUP_DREAM_ADMIN' ),
					'uv.verified' => '1' 
			) )->field ( 'uv.uid' )->findAll ();
			$groupLink = M ( 'UserGroupLink' );
			// 如果原来有管理员，取消其梦想中心管理员角色
			$res = true;
			if ($old) {
				$old_uids=getSubByKey($old,'uid');
				$res = $groupLink->moveGroup ($old_uids, null, C ( 'USER_GROUP_DREAM_ADMIN' ) );
			}
			if ($res) {
				$res = $groupLink->moveGroup ( $uid, C ( 'USER_GROUP_DREAM_ADMIN' ) );
			}
		}
		return $res;
	}
	/**
	 * 获取最后错误信息
	 *
	 * @return string 最后错误信息
	 */
	public function getLastError() {
		return $this->error;
	}
}

?>